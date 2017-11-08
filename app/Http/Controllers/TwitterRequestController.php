<?php

namespace App\Http\Controllers;

use App\TwitterRequest;
use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterRequestController extends Controller
{
    /**
     * 
     */
    public function __construct()
    {
        $this->middleware('twitter.auth')->only(['create', 'store', 'done']);
    }

    /**
     * Initialises a new Twitter Auth request, clearing existing tokens if any exist.
     */
    public function init(TwitterOAuth $twitter)
    {
        if (!empty(session('twitter_oauth_token'))) {
            $this->clearSession(true);

            return redirect()->route('twitter.init');
        }

        $useOob = config('services.twitter.use_oob');
        $callback = ($useOob ? 'oob' : route('twitter.callback'));
        $requestToken = $twitter->oauth('oauth/request_token', ['oauth_callback' => $callback]);
        $resultCode = $twitter->getLastHttpCode();

        if ($resultCode == 200) {
            session([
                'twitter_request_ident' => $requestToken['twitter_oauth_token'],
                'twitter_oauth_token' => $requestToken['twitter_oauth_token'],
                'twitter_oauth_token_secret' => $requestToken['twitter_oauth_token_secret']
            ]);

            $authUrl = $twitter->url('oauth/authorize', ['twitter_oauth_token' => $requestToken['twitter_oauth_token']]);

            if ($useOob) {
                return view('twitter.enter_pin', ['authUrl' => $authUrl]);
            }

            return redirect($authUrl);
        } else {
            session()->flash('error', 'Error connecting to Twitter.');

            return redirect('/');
        }
    }

    /**
     *
     */
    public function confirmKey(Request $request, TwitterOAuth $twitter)
    {
        $inputData = $request->validate([
            'pin_number' => 'required|integer'
        ]);

        $pinNumber = $inputData['pin_number'];
        $accessToken = $twitter->oauth('oauth/access_token', ['oauth_verifier' => $pinNumber]);
        $resultCode = $twitter->getLastHttpCode();

        if ($resultCode == 200) {
            session(['twitter_access_token' => $accessToken]);

            $this->clearSession();

            return redirect()->route('twitter.create');
        } else {
            session()->flash('error', 'Unable to verify Twitter credentials.');

            return redirect('/');
        }
    }

    /**
     *
     */
    public function callbackHandler(Request $request, TwitterOAuth $twitter)
    {
        if ($request->input('denied', false) || empty($request->input('oauth_verifier', null))) {
            $this->clearSession(true);
            session()->flash('error', 'Twitter auth denied!');

            return redirect('/');
        }

        $requestToken = $this->getSessionRequestToken();

        if (empty($requestToken['twitter_oauth_token']) || $requestToken['twitter_oauth_token'] !== $request->input('twitter_oauth_token', '')) {
            $this->clearSession(true);
            session()->flash('error', 'Incorrect OAuth token!');

            return redirect('/');
        }

        $oauthVerifier = $request->input('oauth_verifier');
        $accessToken = $twitter->oauth('oauth/access_token', ['oauth_verifier' => $oauthVerifier]);
        $resultCode = $twitter->getLastHttpCode();

        if ($resultCode == 200) {
            session(['twitter_access_token' => $accessToken]);

            $this->clearSession();

            return redirect('twitter.create');
        } else {
            session()->flash('error', 'Unable to verify Twitter credentials.');

            return redirect('/');
        }
    }

    /**
     *
     */
    public function create()
    {
        return view('twitter.create');
    }

    /**
     *
     */
    public function store(Request $request)
    {
        $validatedInput = $request->validate([
            'twitterUser' => 'required|string|alpha_dash',
            'userEmail' => 'required|string|email'
        ]);

        $validatedInput['request_ident'] = session('twitter_request_ident');
        $validatedInput['access_token'] = session('twitter_access_token');

        // TODO: Verify "request_ident"

        $twitterRequest = TwitterRequest::create($validatedInput);

        $twitterRequest->save();

        return redirect()->route('twitter.done', ['twitterRequest' => $twitterRequest])

        // TODO: Create and store TwitterRequest
        // TODO: Add sending-task to local queue
        // TODO: Redirect to verification page
    }

    /**
     *
     */
    public function done(TwitterRequest $twitterRequest)
    {
        // TODO: Display the details of the request

        return view('twitter.done', ['twitterRequest' => $twitterRequest]);
    }

    /**
     *
     */
    public function missingAuth()
    {
        return view('twitter.missingAuth');
    }

    private function getSessionRequestToken()
    {
        $request_token = [];
        $request_token['twitter_oauth_token'] = session('twitter_oauth_token');
        $request_token['twitter_oauth_token_secret'] = session('twitter_oauth_token_secret');

        return $request_token;
    }

    private function clearSession($all = false)
    {
        session()->forget(['twitter_oauth_token', 'twitter_oauth_token_secret']);

        if ($all) {
            session()->forget(['twitter_access_token', 'twitter_request_ident']);
        }
    }
}
