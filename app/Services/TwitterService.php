<?php

namespace App\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class TwitterService
{
    protected $twitter;

    public function __construct()
    {
        $this->twitter = new TwitterOAuth(
            env('X_API_KEY'),
            env('X_API_SECRET'),
            env('X_ACCESS_TOKEN'),
            env('X_ACCESS_SECRET')
        );
    }

    public function save_access_oauth_token_depreciated($oauthToken, $oauthVerifier)
    {
        $accessToken = $this->twitter->oauth('oauth/access_token', [
            'oauth_token' => $oauthToken,
            'oauth_verifier' => $oauthVerifier
        ]);


        Log::info('Access Token: ', $accessToken);
        Session::put('access_oauth_token', $accessToken['oauth_token']);
        Session::put('access_oauth_token_secret', $accessToken['oauth_token_secret']);
    }

    public function save_access_oauth_token($oauthToken, $oauthVerifier)
    {
        Session::put('pre_oauth_token', $oauthToken);
        Session::put('oauthVerifier', $oauthVerifier);
    }

    public function getUserDetails($oauthToken, $oauthVerifier)
    {
        $accessToken = $this->twitter->oauth('oauth/access_token', [
            'oauth_token' => $oauthToken,
            'oauth_verifier' => $oauthVerifier
        ]);

        Session::put('access_oauth_token', $accessToken['oauth_token']);
        Session::put('access_oauth_token_secret', $accessToken['oauth_token_secret']);

        $oauthNonce = bin2hex(random_bytes(16));
        $oauthTimestamp = time();
        $oauthSignatureMethod = 'HMAC-SHA1';
        $oauthVersion = '1.0';
        $baseString = "GET&" . urlencode('https://api.twitter.com/1.1/account/verify_credentials.json') .
            "&" . urlencode("oauth_consumer_key=" . config('services.twitter.client_id') . "&" . "oauth_nonce=" .
                $oauthNonce . "&" . "oauth_signature_method=" . $oauthSignatureMethod . "&" . "oauth_timestamp=" .
                $oauthTimestamp . "&" . "oauth_token=" . $accessToken['oauth_token'] . "&" . "oauth_version=" . $oauthVersion);
        $signingKey = urlencode(config('services.twitter.client_secret')) . "&" . urlencode($accessToken['oauth_token_secret']);
        $oauthSignature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
        $response = Http::withOptions(['verify' => false])->withHeaders(
            ['Authorization' => 'OAuth ' . 'oauth_consumer_key="' . config('services.twitter.client_id') .
                '", ' . 'oauth_nonce="' . $oauthNonce . '", ' . 'oauth_signature="' . urlencode($oauthSignature) .
                '", ' . 'oauth_signature_method="' . $oauthSignatureMethod . '", ' . 'oauth_timestamp="' . $oauthTimestamp . '", ' .
                'oauth_token="' . $accessToken['oauth_token'] . '", ' . 'oauth_version="' . $oauthVersion . '"']
        )
            ->get('https://api.twitter.com/1.1/account/verify_credentials.json');

        if ($response->failed()) {
            return response()->json(['message' => $response->body()], 500);
        }
        return $response->json();
    }

    public function getUserDetails2($oauthToken, $oauthVerifier)
    {
        $accessToken = $this->twitter->oauth('oauth/access_token', [
            'oauth_token' => $oauthToken,
            'oauth_verifier' => $oauthVerifier
        ]);

        Log::info('Access Token: ', $accessToken);

        $this->twitter->setOauthToken($accessToken['oauth_token'], $accessToken['oauth_token_secret']);
        $response = $this->twitter->get('account/verify_credentials');
        if (isset($response->errors)) {
            Log::error('Twitter API Error: ', (array) $response->errors);
        }
        Log::info('Twitter Response: ', (array) $response);
        return $response;
    }

    public function verifyCredentials2()
    {
        $access_oauth_token = Session::get('access_oauth_token');
        $access_oauth_token_secret = Session::get('oauth_token_secret');

        $oauthNonce = bin2hex(random_bytes(16));
        $oauthTimestamp = time();
        $oauthSignatureMethod = 'HMAC-SHA1';
        $oauthVersion = '1.0';
        $baseString = "GET&" . urlencode('https://api.twitter.com/1.1/account/verify_credentials.json') .
            "&" . urlencode("oauth_consumer_key=" . config('services.twitter.client_id') . "&" . "oauth_nonce=" .
                $oauthNonce . "&" . "oauth_signature_method=" . $oauthSignatureMethod . "&" . "oauth_timestamp=" .
                $oauthTimestamp . "&" . "oauth_token=" . $access_oauth_token . "&" . "oauth_version=" . $oauthVersion);
        $signingKey = urlencode(config('services.twitter.client_secret')) . "&" . urlencode($access_oauth_token_secret);
        $oauthSignature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
        $response = Http::withOptions(['verify' => false])->withHeaders(
            ['Authorization' => 'OAuth ' . 'oauth_consumer_key="' . config('services.twitter.client_id') .
                '", ' . 'oauth_nonce="' . $oauthNonce . '", ' . 'oauth_signature="' . urlencode($oauthSignature) .
                '", ' . 'oauth_signature_method="' . $oauthSignatureMethod . '", ' . 'oauth_timestamp="' . $oauthTimestamp . '", ' .
                'oauth_token="' . $access_oauth_token . '", ' . 'oauth_version="' . $oauthVersion . '"']
        )
            ->get('https://api.twitter.com/1.1/account/verify_credentials.json');

        if ($response->failed()) {
            return response()->json(['message' => $response->body()], 500);
        }
        return $response->json();
    }

    public function verifyCredentials()
    {
        $pre_oauth_token = Session::get('pre_oauth_token');
        $oauthVerifier = Session::get('oauthVerifier');

        $accessToken = $this->twitter->oauth('oauth/access_token', [
            'oauth_token' => $pre_oauth_token,
            'oauth_verifier' => $oauthVerifier
        ]);


        Log::info('Access Token (verify credentials): ', $accessToken);
        $access_oauth_token = $accessToken['oauth_token'];
        $access_oauth_token_secret = $accessToken['oauth_token_secret'];

        $oauthNonce = bin2hex(random_bytes(16));
        $oauthTimestamp = time();
        $oauthSignatureMethod = 'HMAC-SHA1';
        $oauthVersion = '1.0';
        $baseString = "GET&" . urlencode('https://api.twitter.com/1.1/account/verify_credentials.json') .
            "&" . urlencode("oauth_consumer_key=" . config('services.twitter.client_id') . "&" . "oauth_nonce=" .
                $oauthNonce . "&" . "oauth_signature_method=" . $oauthSignatureMethod . "&" . "oauth_timestamp=" .
                $oauthTimestamp . "&" . "oauth_token=" . $access_oauth_token . "&" . "oauth_version=" . $oauthVersion);
        $signingKey = urlencode(config('services.twitter.client_secret')) . "&" . urlencode($access_oauth_token_secret);
        $oauthSignature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
        $response = Http::withOptions(['verify' => false])->withHeaders(
            ['Authorization' => 'OAuth ' . 'oauth_consumer_key="' . config('services.twitter.client_id') .
                '", ' . 'oauth_nonce="' . $oauthNonce . '", ' . 'oauth_signature="' . urlencode($oauthSignature) .
                '", ' . 'oauth_signature_method="' . $oauthSignatureMethod . '", ' . 'oauth_timestamp="' . $oauthTimestamp . '", ' .
                'oauth_token="' . $access_oauth_token . '", ' . 'oauth_version="' . $oauthVersion . '"']
        )
            ->get('https://api.twitter.com/1.1/account/verify_credentials.json');

        if ($response->failed()) {
            return response()->json(['message' => $response->body()], 500);
        }
        return $response->json();
    }

    public function send_direct_messsage_2($recipient_id, $message)
    {

        $access_oauth_token = Session::get('access_oauth_token');
        $access_oauth_token_secret = Session::get('oauth_token_secret');

        $data = ['event' =>
        [
            'type' => 'message_create',
            'message_create' => [
                'target' => ['recipient_id' => $recipient_id],
                'message_data' => ['text' => $message]
            ]
        ]];

        $this->twitter->setOauthToken($access_oauth_token, $access_oauth_token_secret);
        $result = $this->twitter->post('direct_messages/events/new', $data);
        if ($this->twitter->getLastHttpCode() != 200) {
            return ['message' => $this->twitter->getLastHttpCode() . " :Error sending message: " . $this->twitter->getLastBody()];
        }

        return ['message' => 'Sent Successfully'];
    }

    public function send_direct_messsage($recipient_id, $message)
    {
        $pre_oauth_token = Session::get('pre_oauth_token');
        $oauthVerifier = Session::get('oauthVerifier');

        $accessToken = $this->twitter->oauth('oauth/access_token', [
            'oauth_token' => $pre_oauth_token,
            'oauth_verifier' => $oauthVerifier
        ]);


        Log::info('Access Token (DMS): ', $accessToken);
        $access_oauth_token = $accessToken['oauth_token'];
        $access_oauth_token_secret = $accessToken['oauth_token_secret'];
        Log::info('sessions:', [$access_oauth_token, $access_oauth_token_secret]);
        $oauthNonce = bin2hex(random_bytes(16));
        $oauthTimestamp = time();
        $oauthSignatureMethod = 'HMAC-SHA1';
        $oauthVersion = '1.0';
        $baseString = "POST&" . urlencode('https://api.x.com/1.1/direct_messages/events/new.json') . "&" .
            urlencode("oauth_consumer_key=" . config('services.twitter.client_id') . "&" . "oauth_nonce=" .
                $oauthNonce . "&" . "oauth_signature_method=" . $oauthSignatureMethod . "&" . "oauth_timestamp=" .
                $oauthTimestamp . "&" . "oauth_token=" . $access_oauth_token . "&" . "oauth_version=" . $oauthVersion);
        $signingKey = urlencode(config('services.twitter.client_secret')) . "&" .
            urlencode($access_oauth_token_secret);
        $oauthSignature = base64_encode(hash_hmac('sha1', $baseString, $signingKey, true));
        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Authorization' => 'OAuth ' . 'oauth_consumer_key="' .
                    config('services.twitter.client_id') . '", ' . 'oauth_nonce="' . $oauthNonce . '", ' .
                    'oauth_signature="' . urlencode($oauthSignature) . '", ' . 'oauth_signature_method="' .
                    $oauthSignatureMethod . '", ' . 'oauth_timestamp="' . $oauthTimestamp . '", ' . 'oauth_token="' .
                    $access_oauth_token . '", ' . 'oauth_version="' . $oauthVersion . '"',
                'Content-Type' => 'application/json'
            ])
            ->post(
                'https://api.x.com/1.1/direct_messages/events/new.json',
                ['event' => [
                    'type' => 'message_create',
                    'message_create' => [
                        'target' => ['recipient_id' => $recipient_id],
                        'message_data' => ['text' => $message]
                    ]
                ]]
            );

        return $response;
    }
}
