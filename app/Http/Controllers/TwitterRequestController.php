<?php

namespace App\Http\Controllers;

use App\TwitterRequest;
use App\Jobs\ForwardTwitterRequest;
use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;

class TwitterRequestController extends Controller
{
    /**
     * 
     */
    public function __construct()
    {
        $this->middleware('twitter.auth')->only(['create', 'store']);
    }

    /**
     * Initialises a new Twitter Auth request, clearing existing tokens if any exist.
     */
    public function init(TwitterOAuth $twitter)
    {
        if (!empty(session('twitter_oauth_token'))) {
            $this->clearSessionVars(true);

            return redirect()->route('twitter.init');
        }

        $useOob = config('services.twitter.use_oob');
        $callback = ($useOob ? 'oob' : route('twitter.callback'));
        $requestToken = $twitter->oauth('oauth/request_token', ['oauth_callback' => $callback]);
        $resultCode = $twitter->getLastHttpCode();

        if ($resultCode == 200) {
            session([
                'twitter_request_ident' => $requestToken['oauth_token'],
                'twitter_oauth_token' => $requestToken['oauth_token'],
                'twitter_oauth_token_secret' => $requestToken['oauth_token_secret']
            ]);

            $authUrl = $twitter->url('oauth/authorize', ['oauth_token' => $requestToken['oauth_token']]);

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

            $this->clearSessionVars();

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
            $this->clearSessionVars(true);
            session()->flash('error', 'Twitter auth denied!');

            return redirect('/');
        }

        $requestToken = $this->getSessionRequestToken();

        if (empty($requestToken['twitter_oauth_token']) || $requestToken['twitter_oauth_token'] !== $request->input('twitter_oauth_token', '')) {
            $this->clearSessionVars(true);
            session()->flash('error', 'Incorrect OAuth token!');

            return redirect('/');
        }

        $oauthVerifier = $request->input('oauth_verifier');
        $accessToken = $twitter->oauth('oauth/access_token', ['oauth_verifier' => $oauthVerifier]);
        $resultCode = $twitter->getLastHttpCode();

        if ($resultCode == 200) {
            session(['twitter_access_token' => $accessToken]);

            $this->clearSessionVars();

            return redirect('twitter.create');
        } else {
            session()->flash('error', 'Unable to verify Twitter credentials.');

            return redirect('/');
        }
    }

    public function test()
    {
        return view('twitter.test');
    }

    public function vueCheck(Request $request)
    {
        $validatedData = $request->validate([
            'twitter_user' => 'required|string|alpha_dash',
        ]);

        // TODO: Contact the DB server
        $hasRecent = false;
        $twitterLink = '';

        if (!$hasRecent) {
            if (!empty(session('twitter_oauth_token'))) {
                $this->clearSessionVars(true);
    
                return redirect()->route('twitter.init');
            }
    
            $useOob = config('services.twitter.use_oob');
            $callback = ($useOob ? 'oob' : route('twitter.callback'));
            $requestToken = $twitter->oauth('oauth/request_token', ['oauth_callback' => $callback]);
            $resultCode = $twitter->getLastHttpCode();
    
            if ($resultCode == 200) {
                session([
                    'twitter_request_ident' => $requestToken['oauth_token'],
                    'twitter_oauth_token' => $requestToken['oauth_token'],
                    'twitter_oauth_token_secret' => $requestToken['oauth_token_secret']
                ]);
    
                $authUrl = $twitter->url('oauth/authorize', ['oauth_token' => $requestToken['oauth_token']]);
    
                if ($useOob) {
                    return view('twitter.enter_pin', ['authUrl' => $authUrl]);
                }
    
                return redirect($authUrl);
            } else {
                session()->flash('error', 'Error connecting to Twitter.');
    
                return redirect('/');
            }
        }

        return response()->json([
            'hasRecent' => $hasRecent,
            'twitterLink' => $twitterLink,
            'redirectTo' => 'https://www.twitter.com/' . $validatedData['twitter_user'],
        ]);
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
            'twitter_username' => 'required|string|alpha_dash',
            'email' => 'required|string|email'
        ]);

        $validatedInput['request_ident'] = session('twitter_request_ident');
        $validatedInput['access_token'] = json_encode(session('twitter_access_token'));

        // TODO: Verify "request_ident"

        $twitterRequest = TwitterRequest::create($validatedInput);

        $twitterRequest->save();

        $this->clearSessionVars(true);
        
        return redirect()->route('twitter.done', ['twitterRequest' => $twitterRequest]);
    }

    /**
     *
     */
    public function done(TwitterRequest $twitterRequest)
    {
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

    private function clearSessionVars($all = false)
    {
        session()->forget(['twitter_oauth_token', 'twitter_oauth_token_secret']);

        if ($all) {
            session()->forget(['twitter_access_token', 'twitter_request_ident']);
        }
    }
}
