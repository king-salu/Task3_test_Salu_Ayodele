<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use League\OAuth2\Client\Provider\GenericProvider;

class TwitterController extends Controller
{

    protected $provider;
    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId' => config('services.twitter.client_id'),
            'clientSecret' => config('services.twitter.client_secret'),
            'redirectUri' => config('services.twitter.redirect'),
            'urlAuthorize' => 'https://twitter.com/i/oauth2/authorize',
            'urlAccessToken' => 'https://api.twitter.com/oauth2/token',
            'urlResourceOwnerDetails' => 'https://api.twitter.com/2/users/me',
        ]);
    }

    /** * Redirect the user to the Twitter authentication page. 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function redirectToTwitter()
    {
        Log::info('redirectToTwitter ?', ['did you get here?']);
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        Session::put('oauth2state', $this->provider->getState());
        Log::info('redirectToTwitter', ['oauth2state' => Session::get('oauth2state')]);
        return redirect($authorizationUrl);
    }

    /** * Obtain the user information from Twitter. 
     * 
     * @return \Illuminate\Http\Response 
     **/
    public function handleTwitterCallback()
    {
        // Check for errors 
        if (request()->get('error')) {
            return response()->json(['error' => request()->get('error')], 400);
        }

        Log::info('handleTwitterCallback', ['oauth2state' => Session::get('oauth2state'), 'received_state' => request()->get('state')]);
        // Validate state 
        if (empty(request()->get('state')) || (request()->get('state') !== Session::get('oauth2state'))) {
            Session::forget('oauth2state');
            return response()->json(['error' => 'Invalid state'], 400);
        }

        try {
            // Get the access token 
            $accessToken = $this->provider->getAccessToken('authorization_code', ['code' => request()->get('code')]);
            // Get the resource owner 
            $resourceOwner = $this->provider->getResourceOwner($accessToken);
            // Get user details 
            $user = $resourceOwner->toArray();
            return response()->json(['user' => $user, 'access_token' => $accessToken->getToken()]);
        } catch (Exception $ex) {
            Log::error('Twitter OAuth callback error', ['message' => $ex->getMessage(), 'trace' => $ex->getTraceAsString(),]);
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    public function getTokenRough()
    {
        $oauth_nonce = bin2hex(random_bytes(16));
        $oauth_timestamp = time();
        $oauth_signature_method = 'HMAC-SHA1';
        $oauth_version = '1.0';
        $base_string = "POST&" . urlencode('https://api.twitter.com/oauth/request_token') . "&" . urlencode("oauth_callback=" . urlencode(config('services.twitter.redirect')) . "&" . "oauth_consumer_key=" . config('services.twitter.client_id') . "&" . "oauth_nonce=" . $oauth_nonce . "&" . "oauth_signature_method=" . $oauth_signature_method . "&" . "oauth_timestamp=" . $oauth_timestamp . "&" . "oauth_version=" . $oauth_version);
        $signing_key = urlencode(config('services.twitter.client_secret')) . "&";
        $oauth_signature = base64_encode(hash_hmac('sha1', $base_string, $signing_key, true));
        $response = Http::withOptions(['verify' => false])->withHeaders(['Authorization' => 'OAuth ' . 'oauth_callback="' . urlencode(config('services.twitter.redirect')) . '", ' . 'oauth_consumer_key="' . config('services.twitter.client_id') . '", ' . 'oauth_nonce="' . $oauth_nonce . '", ' . 'oauth_signature="' . urlencode($oauth_signature) . '", ' . 'oauth_signature_method="' . $oauth_signature_method . '", ' . 'oauth_timestamp="' . $oauth_timestamp . '", ' . 'oauth_version="' . $oauth_version . '"'])->asForm()->post('https://api.twitter.com/oauth/request_token');
        if ($response->failed()) {
            return response()->json(['message' => 'Failed to get request token'], 500);
        }
        parse_str($response->body(), $data);
        return response()->json($data);
    }

    public function getToken(Request $request)
    {
        try {
            $oauth_nonce = bin2hex(random_bytes(16));
            $oauth_timestamp = time();
            $oauth_signature_method = 'HMAC-SHA1';
            $oauth_version = '1.0';
            $base_string = "POST&" . urlencode('https://api.twitter.com/oauth/request_token') . "&" . urlencode("oauth_callback=" . urlencode(config('services.twitter.redirect')) . "&" . "oauth_consumer_key=" . config('services.twitter.client_id') . "&" . "oauth_nonce=" . $oauth_nonce . "&" . "oauth_signature_method=" . $oauth_signature_method . "&" . "oauth_timestamp=" . $oauth_timestamp . "&" . "oauth_version=" . $oauth_version);
            $signing_key = urlencode(config('services.twitter.client_secret')) . "&";
            $oauth_signature = base64_encode(hash_hmac('sha1', $base_string, $signing_key, true));
            $response = Http::withOptions(['verify' => false])->withHeaders(['Authorization' => 'OAuth ' . 'oauth_callback="' . urlencode(config('services.twitter.redirect')) . '", ' . 'oauth_consumer_key="' . config('services.twitter.client_id') . '", ' . 'oauth_nonce="' . $oauth_nonce . '", ' . 'oauth_signature="' . urlencode($oauth_signature) . '", ' . 'oauth_signature_method="' . $oauth_signature_method . '", ' . 'oauth_timestamp="' . $oauth_timestamp . '", ' . 'oauth_version="' . $oauth_version . '"'])->asForm()->post('https://api.twitter.com/oauth/request_token');
            if ($response->failed()) {
                return response()->json(['message' => 'Failed to get request token'], 500);
            }
            parse_str($response->body(), $data);

            // $arr_data = json_decode($data, true);
            // Store the request token in the session 
            Session::put('oauth_token', $data['oauth_token']);
            Session::put('oauth_token_secret', $data['oauth_token_secret']);
            // Cookie::queue('oauth_token', $data['oauth_token']);
            // Cookie::queue('oauth_token_secret', $data['oauth_token_secret']);
            Log::info('Request token stored in session', [
                'oauth_token' => Session::get('oauth_token'),
                'oauth_token_secret' => Session::get('oauth_token_secret'),
                'session_id' => session()->getId(),
            ]);
            return response()->json($data);
        } catch (Exception $ex) {
            Log::error('Twitter request token error', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json(['message' => 'An error occurred during Twitter authentication.'], 500);
        }
    }

    public function handleRedirect()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function handleCallbackSocialite(Request $request)
    {
        try {
            // Retrieve the request token from the session 
            $oauthToken = Session::get('oauth_token');
            $oauthTokenSecret = Session::get('oauth_token_secret');
            if (!$oauthToken || !$oauthTokenSecret) {
                throw new Exception('Missing temporary OAuth credentials on Session.');
            }
            $user = Socialite::driver('twitter')->userFromTokenAndSecret($oauthToken, $oauthTokenSecret);
            $token = $user->token;

            return response()->json(['oauth_token' => $token]);
        } catch (Exception $ex) {
            Log::error('Twitter OAuth callback error', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            // Retrieve the request token from the session 
            $oauthToken = Session::get('oauth_token');
            $oauthTokenSecret = Session::get('oauth_token_secret');
            if (!$oauthToken || !$oauthTokenSecret) {
                throw new Exception('Missing temporary OAuth credentials on Session.');
            }
            $oauth_nonce = bin2hex(random_bytes(16));
            $oauth_timestamp = time();
            $oauth_signature_method = 'HMAC-SHA1';
            $oauth_version = '1.0';
            $base_string = "POST&" . urlencode('https://api.x.com/1.1/account/verify_credentials.json') . "&" . urlencode("oauth_consumer_key=" . config('services.twitter.client_id') . "&" . "oauth_nonce=" . $oauth_nonce . "&" . "oauth_signature_method=" . $oauth_signature_method . "&" . "oauth_timestamp=" . $oauth_timestamp . "&" . "oauth_token=" . $oauthToken . "&" . "oauth_version=" . $oauth_version);
            $signing_key = urlencode(config('services.twitter.client_secret')) . "&" . urlencode($oauthTokenSecret);
            $oauth_signature = base64_encode(hash_hmac('sha1', $base_string, $signing_key, true));


            $response = Http::withOptions(['verify' => false])->withHeaders([
                'Authorization' => 'OAuth ' .
                    'oauth_consumer_key="' . config('services.twitter.client_id') . '", ' .
                    'oauth_nonce="' . $oauth_nonce . '", ' .
                    'oauth_signature="' . urlencode($oauth_signature) . '", ' .
                    'oauth_signature_method="' . $oauth_signature_method . '", ' .
                    'oauth_timestamp="' . $oauth_timestamp . '", ' .
                    'oauth_token="' . $oauthToken . '", ' .
                    'oauth_version="' . $oauth_version . '"'
            ])->get('https://api.x.com/1.1/account/verify_credentials.json');

            if ($response->failed()) {
                // return response()->json(['message' => 'Failed to verify credentials'], 500);
                return response()->json(['message' => $response->body()], 500);
            }

            $userData = json_decode($response->body(), true);

            $token = $userData['oauth_token'];
            return response()->json(['oauth_token' => $token]);
        } catch (Exception $ex) {
            Log::error('Twitter OAuth callback error', [
                'message' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }
}
