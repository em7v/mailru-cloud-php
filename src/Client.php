<?php

namespace Friday14\CloudMailRu;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\MultipartStream;
use SplFileObject;

/**
 * Client to work with https://cloud.mail.ru
 *
 * @author Evgeniy Marthenov
 * @author Evgeniy Marthenov <evgeniy.marthenov@gmail.com>
 *
 */

class Client
{
    /**
     * Consts
     */
    const VERSION_API = 2;
    const CLOUD_DOMAIN = 'https://cloud.mail.ru/api/v2';
    const FETCH_TOKEN_URL = 'https://cloud.mail.ru/api/v2/tokens/csrf';
    const UPLOAD_URL = 'https://cloclo3-upload.cloud.mail.ru/upload/';
    const DOWNLOAD_URL = 'https://cloclo27.datacloudmail.ru/';

    protected $client;

    //Traits
    use AuthTrait;


    public function __construct($login, $password, $domain)
    {
        $this->login = $login;
        $this->password = $password;
        $this->domain = $domain;

        $this->client = new GuzzleClient(
            [
                'headers' => [
                    'Accept' => '*/*',
                    'User-Agent' => $this->userAgent
                ],
                'cookies' => new \GuzzleHttp\Cookie\CookieJar()
            ]);
        $this->authorization();
    }


    /**
     * Move the file
     *
     * @param string $path Path to file
     * @param string $newFolder Path to new folder
     * @return mixed Request response
     */
    public function move($path, $newFolder)
    {
        return $this->request('/file/move', 'POST', [
            'folder' => $newFolder,
            'conflict' => 'rename',
            'home' => $path,
        ]);
    }

    /**
     * Copy file to folder
     *
     * @param string $path Path the file
     * @param string $copyToFolder Copy to this folder
     * @return mixed
     */
    public function copy($path, $copyToFolder)
    {
        return $this->request('/file/copy', 'POST', [
            'folder' => $copyToFolder,
            'conflict' => 'rename',
            'home' => $path,
        ]);
    }


    /**
     * Delete this file
     *
     * @param string $path Path this file which need delete
     * @return mixed
     */
    public function delete($path)
    {
        return $this->request('/file/remove', 'POST', ['home' => $path]);
    }


    /**
     * Get all files which in this folder
     *
     * @param $directory
     * @return mixed
     */
    public function files($directory)
    {
        return $this->request('/folder', 'GET', [
            'home' => $directory,
            'sort' => '{"type":"name","order":"asc"}'
        ]);
    }


    /**
     * @param $path
     * @param $content
     * @return mixed
     */
    public function createFile($path, $content)
    {
        $tmpfile = tmpfile();
        fwrite($tmpfile, $content);
        $tmpfilePath = stream_get_meta_data($tmpfile);
        $file = new SplFileObject($tmpfilePath['uri']);
        return $this->upload($file, $path);
    }


    /**
     * Create a folder in Cloud
     *
     * @param string $path Path new folder
     * @return mixed
     */
    public function createFolder($path)
    {
        return $this->request('/folder/add', 'GET', [
            'home' => $path
        ]);
    }


    /**
     * Rename file
     *
     * @param string $path
     * @param string $name
     * @return mixed
     */
    public function rename($path, $name)
    {
        return $this->request('/file/rename', 'GET', [
            'home' => $path,
            'name' => $name
        ]);
    }


    /**
     * Uploads your files in Cloud
     *
     * @param SplFileObject $file
     * @param string|null $filename
     * @param string $saveFolder
     * @return mixed
     */
    public function upload(SplFileObject $file, $filename = null, $saveFolder = '/')
    {
        $fileName = ($filename == null) ? $file->getBasename() : $filename;
        $fileSize = $file->getSize();
        $stream = fopen($file->getRealPath(), 'r');
        $boundary = uniqid($fileName . $fileSize);

        $params = [
            'query' => [
                'cloud_domain' => 2,
                'x-email' => $this->email,
                'fileapi15004124608926' => ''
            ],
            'body' => new MultipartStream([
                [
                    'name' => 'file',
                    'contents' => $stream,
                ]
            ], $boundary),
            'headers' => [
                'Content-Disposition' => 'form-data; name="file"; filename="epp.txt"',
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
            ]
        ];

        $res = $this->client->request('POST', self::UPLOAD_URL, $params);
        $hash = strstr($res->getBody()->getContents(), ';', true);

        return $this->confirmUpload($saveFolder, $fileName, $hash, $fileSize);
    }


    /**
     * Download your files of Cloud
     *
     * @param string $path File which the your want download
     * @param string $savePath Local Path for save the file
     * @return bool
     */
    public function download($path, $savePath)
    {
        $res = $this->client->request('GET', self::DOWNLOAD_URL . "get{$path}", ['sink' => $_SERVER['DOCUMENT_ROOT'] . $savePath]);
        return $res->getStatusCode() === 200;
    }


    /**
     * Set publish flag a file or folder
     *
     * @param string $path
     * @return mixed
     */
    public function publishFile($path)
    {
        return $this->request('/file/publish', 'GET', [
            'home' => $path
        ]);
    }


    /**
     * Set publish flag and get public link a file
     * @param string $path Path file/folder
     * @return string Public link the file
     */
    public function getLink($path)
    {
        $link = $this->publishFile($path)->body;
        return self::DOWNLOAD_URL . 'weblink/thumb/xw1/' . $link;
    }


    protected function confirmUpload($folder, $filename, $hash, $filesize)
    {
        return $this->request('/file/add', 'POST', [
            'home' => $folder . '/' . $filename,
            'hash' => $hash,
            'size' => $filesize,
            'conflict' => 'rename'
        ]);
    }

    protected function request($uri, $method, array $params, $enctype = null, $defaultParams = true)
    {
        $url = $this->formatUrl($uri);

        $default = $this->structureRequestParams();
        $payload = ($defaultParams) ? array_merge($default, $params) : $params;

        if ($enctype == 'multipart')
            $params = $this->formatMultipartData($payload);
        else
            $params = (strtoupper($method) == 'GET') ? ['query' => $payload] : ['form_params' => $payload];

        $res = $this->client->request($method, $url, $params);
        return json_decode($res->getBody()->getContents());
    }


    protected function formatUrl($uri)
    {
        return (strstr($uri, '://')) ? $uri : self::CLOUD_DOMAIN . $uri;
    }


    protected function formatMultipartData($data)
    {
        $result = [];
        foreach ($data as $key => $datum)
            $result[] = [
                'name' => $key,
                'contents' => $datum
            ];
        return ['multipart' => $result];
    }


    protected function structureRequestParams()
    {
        return [
            'home' => null,
            'api' => self::VERSION_API,
            'email' => $this->email,
            'x-email' => $this->email,
            'token' => $this->token,
            '_' => $this->tokenLifeTime,
        ];
    }
}