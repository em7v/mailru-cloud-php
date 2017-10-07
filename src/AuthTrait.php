<?php

namespace Friday14\Mailru;


trait AuthTrait
{
    
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
