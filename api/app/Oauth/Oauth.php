<?php

namespace App\Oauth;

use Illuminate\Encryption\Encrypter;
use App\Client;

class Oauth
{
    protected $cipher;

    public function __construct($cipher = 'AES-256-CBC')
    {
        $this->cipher = $cipher;
    }

    public function getAccessToken($data)
    {
        $clientId = @$data['client_id'] ?: null;
        $clientSecret = @$data['client_secret'] ?: null;
        $grantType = @$data['grant_type'] ?: null;

        if ($grantType != 'client_credentials') {
            throw new \Exception('Oauth library currently supports client_credentials grant types only');
        }

        $key = hex2bin($clientSecret);
        $crypt = new Encrypter($key, $this->cipher);

        $payload = ['c_id' => $clientId];
        $signature = $crypt->encrypt($payload);
        $accessToken = sprintf("%s.%s", base64_encode(json_encode($payload)), $signature);

        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
        ];
    }

    public function verifyAccessToken($token)
    {
        $token = explode('.', $token);
        $token[0] = str_replace("Bearer ", "", $token[0]);
        $data = json_decode(base64_decode($token[0]), true);

        $client = Client::where('client_id', $data['c_id'])->first();

        $key = hex2bin($client['client_secret']);
        $crypt = new Encrypter($key, $this->cipher);

        $plain = $crypt->decrypt($token[1]);
        // $plain = json_decode($plain, true);

        return ($data === $plain);
    }
}
