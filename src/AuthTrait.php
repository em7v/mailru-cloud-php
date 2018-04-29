<?php

namespace Friday14\Mailru;


trait AuthTrait
{
    protected $userAgent = 'Mozilla / 5.0(Windows; U; Windows NT 5.1; en - US; rv: 1.9.0.1) Gecko / 2008070208 Firefox / 3.0.1';
    protected $isAuth;
    protected $email;
    protected $login;
    protected $password;
    protected $domain;

    protected $token;
    protected $tokenLifeTime;

    protected function authorization()
    {

        $this->request('https://auth.mail.ru/cgi-bin/auth', 'POST', [
            'Login' => $this->login,
            'Password' => $this->password,
            'Domain' => $this->domain,
        ], 'multipart', false);

        $this->isAuth = true;
        $this->client->request('GET', 'https://cloud.mail.ru');
        $this->fetchToken();
        return $this;
    }


    public function fetchToken()
    {
        $res = $this->client->request('GET', self::FETCH_TOKEN_URL, [
            'form_params' => [
                'api' => 'v2',
                'email' => $this->login,
                'x-email' => $this->login,
            ]
        ]);
        $this->parseToken($res->getBody()->getContents());
    }


    protected function parseToken($token)
    {
        $data = json_decode($token);
        $this->token = $data->body->token;
        $this->tokenLifeTime = $data->time;
        $this->email = $data->email;
    }
}