<?php

namespace App\Http\Controllers;

use App\TwitterRequest;
use App\Jobs\ForwardTwitterRequest;
use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;

class TwitterRequestController extends Controller
{
    /**
     * Instantiate controller and setup middleware.
     */
    public function __construct()
    {
        $this->middleware('twitter.auth')->only(['create', 'store']);
    }

    /**
     * Initialises a new Twitter Auth request, clearing existing tokens if any exist.
     *
     * @param  TwitterOAuth $twitter
     * @return \Illuminate\Http\Response
     * @deprecated
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
     * @deprecated
     */
    public function confirmKey(Request $request, TwitterOAuth $twitter)
    {
        $validatedInput = $request->validate([
            'pin_number' => 'required|integer'
        ]);

        $pinNumber = $validatedInput['pin_number'];
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
     * @deprecated
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

    public function show($id)
    {
        return view('twitter.show')->with(['twitterID' => $id]);
    }

    public function vueCheck(Request $request)
    {
        $validatedData = $request->validate([
            'twitter_user' => 'required|string|alpha_dash',
        ]);

        // TODO: Consider adding another route to the DB server that
        //       doesn't return the full result.
        $guzzle = new GuzzleClient(['base_uri' => config('ew.ewdb.url')]);
        $response = $guzzle->request(
            'GET',
            '/api/twitter/' . $validatedData['twitter_user'],
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . config('ew.ewdb.token'),
                ],
            ]
        );

        $hasRecent = false;
        $redirectTo = '';
        $twitterLink = '';

        if ($response->getStatusCode() === 200) {
            $body = $response->getBody();

            if (!empty($body) && !empty($body = json_decode($body))) {
                $hasRecent = true;

                // TODO: Figure out what the format should be like
                $redirectTo = route('twitter.result', ['id' => $body['twitterID']]);
            }
        }

        /*
        [1:59 PM] James Don: localhost:62020/api/AnalyzeTwitterAccount
        [2:00 PM] James Don: Token, Name, RequesterName, Secret
        */

        if (!$hasRecent) {
            if (!empty(session('twitter_oauth_token'))) {
                $this->clearSessionVars(true);
            }

            $twitter      = resolve('Abraham\TwitterOAuth\TwitterOAuth');
            $requestToken = $twitter->oauth('oauth/request_token', ['oauth_callback' => 'oob']);
            $resultCode   = $twitter->getLastHttpCode();

            if ($resultCode == 200) {
                session([
                    'twitter_request_ident' => $requestToken['oauth_token'],
                    'twitter_oauth_token' => $requestToken['oauth_token'],
                    'twitter_oauth_token_secret' => $requestToken['oauth_token_secret']
                ]);

                $twitterLink = $twitter->url('oauth/authorize', ['oauth_token' => $requestToken['oauth_token']]);
            } else {
                return response()->json(
                    ['errors' => ['Error connecting to Twitter. Please try again later.']],
                    $resultCode);
            }
        }

        return response()->json([
            'hasRecent' => $hasRecent,
            'twitterLink' => $twitterLink,
            'redirectTo' => $redirectTo,
        ]);
    }

    /**
     *
     */
    public function vueCheckPin()
    {
        //
        $validatedInput = $request->validate([
            'pin_number' => 'required|integer',
            'email' => 'required|email',
        ]);

        $twitter     = resolve('Abraham\TwitterOAuth\TwitterOAuth');
        $accessToken = $twitter->oauth('oauth/access_token',
                                        ['oauth_verifier' => $validatedInput['pin_number']]);
        $resultCode  = $twitter->getLastHttpCode();

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
