<?php

namespace App\Http\Controllers;

use App\Services\TwitterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XController extends Controller
{
    protected $twitterService;
    public function __construct(TwitterService $twitterService)
    {
        $this->twitterService = $twitterService;
    }

    public function redirectToTwitter()
    {
        $requestToken = $this->twitterService->twitter->oauth(
            'oauth/request_token',
            ['oauth_callback' => route('twitter.callback')]
        );
        session(['oauth_token' => $requestToken['oauth_token']]);
        session(['oauth_token_secret' => $requestToken['oauth_token_secret']]);
        return redirect($this->twitterService->twitter->url('oauth/authorize', ['oauth_token' => $requestToken['oauth_token']]));
    }

    public function handleTwitterCallback(Request $request)
    {
        Log::info('handleTwitterCallback', ['request' => $request->all()]);
        $oauthToken = $request->input('oauth_token');
        $oauthVerifier = $request->input('oauth_verifier');
        // $userDetails = $this->twitterService->getUserDetails($oauthToken, $oauthVerifier);
        $this->twitterService->save_access_oauth_token($oauthToken, $oauthVerifier);
        // Handle user details (e.g., create or update user in the database) 
        return redirect('/home');
        // return $userDetails;
    }

    public function handleTwitterDM(Request $request)
    {
        $recipient = $request->recipient;
        $message = $request->message;

        if ((empty($recipient)) || (empty($message))) return response()->json(['message' => 'incomplete requirements, recipient or message'], 400);

        return $this->twitterService->send_direct_messsage($recipient, $message);
    }

    public function handleTwitterVerification()
    {
        return $this->twitterService->verifyCredentials();
    }
}
