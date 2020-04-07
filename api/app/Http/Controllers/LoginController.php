<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Oauth\Oauth;
use App\Client;

class LoginController extends BaseController
{
    public function token(Request $request)
    {
        $data = [
            'grant_type' => $request->input('grant_type'),
            'client_id' => $request->input('client_id'),
            'client_secret' => $request->input('client_secret')
        ];

        $client = Client::where('client_id', $data['client_id'])
            ->where('client_secret', $data['client_secret'])
            ->first();

        if (!$client) {
            return response()->json(['message' => 'Unknown client'], 404);
        }

        try {
            $accessToken = (new Oauth())->getAccessToken($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->json($accessToken);
    }
}
