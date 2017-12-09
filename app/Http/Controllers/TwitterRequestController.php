<?php

namespace App\Http\Controllers;

use App\TwitterRequest;
use App\Http\Requests\RequestIdRequest;
use App\Jobs\ForwardTwitterRequest;
use App\Mail\TwitterRequestProcessed;
use App\Services\DataProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
     *
     */
    public function index()
    {
        return view('twitter.index');
    }

    /**
     *
     */
    public function show(RequestIdRequest $request, DataProcessor $processor)
    {
        $result         = null;
        $requestId      = $request->input('request_id');
        $twitterRequest = TwitterRequest::fromRequestId($requestId);

        // Retrieve result from DB server
        $guzzle = new GuzzleClient([
            'base_uri'    => config('ew.ewdb.url'),
            'http_errors' => false,
        ]);

        $response = $guzzle->request(
            'GET',
            '/api/twitter/has/' . $twitterRequest->twitter_username,
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . config('ew.ewdb.token'),
                ],
            ]
        );

        if ($response->getStatusCode() === 200) {
            $recordId = intval($response->getBody()->getContents());

            if ($recordId > 0) {
                $response = $guzzle->request(
                    'GET',
                    '/api/twitter/' . $recordId,
                    [
                        'headers' => [
                            'Accept'        => 'application/json',
                            'Authorization' => 'Bearer ' . config('ew.ewdb.token'),
                        ],
                    ]
                );

                if ($response->getStatusCode() === 200) {
                    $result = json_decode($response->getBody()->getContents(), true);
                }
            }
        }

        if (empty($result)) {
            return response('Request not found!', 404);
        }

        // Process into data sets
        if (!$processor->prepareData($result)) {
            return response('Error processing data!', 500);
        }

        /* --------------
         * Common Options
         * --------------
         */
        $barOptions = [
            'legend' => [
                'display' => false,
            ],
            'scales' => [
                'yAxes' => [[
                    'ticks' => [
                        'beginAtZero' => true,
                    ],
                ]],
            ],
        ];

        $scatterOptions = [
            'legend' => [
                'display' => false,
            ],
            'scales' => [
                'xAxes' => [[
                    'ticks' => [
                        'min' => -10,
                        'max' => 10,
                    ],
                ]],
                'yAxes' => [[
                    'display' => false,
                ]],
            ],
            'tooltips' => [
                'mode' => 'point',
            ],
        ];

        /* ------------------
         * Analysis - Scatter
         * ------------------
         */
        $data = $processor->getChartJsScatterData('analysis');
        $analysisScatter = app()->chartjs
                                ->name('analysisScatter')
                                ->type('scatter')
                                ->size(['width' => 400, 'height' => 150])
                                ->labels($data['labels'])
                                ->datasets($data['datasets'])
                                ->optionsRaw($scatterOptions);

        /* --------------
         * Analysis - Bar
         * --------------
         */
        $data = $processor->getChartJsBarData('analysis', 10);
        $analysisBar = app()->chartjs
                            ->name('analysisBar')
                            ->type('bar')
                            ->size(['width' => 400, 'height' => 150])
                            ->labels($data['labels'])
                            ->datasets($data['datasets'])
                            ->optionsRaw($barOptions);

        /* ------------
         * MI - Scatter
         * ------------
         */
        $data = $processor->getChartJsScatterData('mi');
        $miScatter = app()->chartjs
                            ->name('miScatter')
                            ->type('scatter')
                            ->size(['width' => 400, 'height' => 150])
                            ->labels($data['labels'])
                            ->datasets($data['datasets'])
                            ->optionsRaw($scatterOptions);

        /* --------
         * MI - Bar
         * --------
         */
        $data = $processor->getChartJsBarData('mi', 10);
        $miBar = app()->chartjs
                        ->name('miBar')
                        ->type('bar')
                        ->size(['width' => 400, 'height' => 150])
                        ->labels($data['labels'])
                        ->datasets($data['datasets'])
                        ->optionsRaw($barOptions);

        /* ---------------
         * Sentiment - Bar
         * ---------------
         */
        $data = $processor->getChartJsBarData('sentiment', 10);
        $sentimentBar = app()->chartjs
                                ->name('sentimentBar')
                                ->type('bar')
                                ->size(['width' => 400, 'height' => 150])
                                ->labels($data['labels'])
                                ->datasets($data['datasets'])
                                ->optionsRaw($barOptions);

        /* -----------
         * Media - Bar
         * -----------
         */
        $data = $processor->getChartJsBarData('media', 10);
        $mediaBar = app()->chartjs
                            ->name('mediaBar')
                            ->type('bar')
                            ->size(['width' => 400, 'height' => 150])
                            ->labels($data['labels'])
                            ->datasets($data['datasets'])
                            ->optionsRaw($barOptions);

        return view('twitter.show')
                ->with([
                    'analysisChartScatter'  => $analysisScatter,
                    'analysisChartBar'      => $analysisBar,
                    'miChartScatter'        => $miScatter,
                    'miChartBar'            => $miBar,
                    'sentimentChartBar'     => $sentimentBar,
                    'mediaChartBar'         => $mediaBar,
                ]);
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

    /**
     *
     */
    public function vueCheck(Request $request)
    {
        $validatedData = $request->validate([
            'twitter_user' => 'required|string|alpha_dash',
        ]);

        $guzzle = new GuzzleClient([
            'base_uri'    => config('ew.ewdb.url'),
            'http_errors' => false,
        ]);
        $response = $guzzle->request(
            'GET',
            '/api/twitter/has/' . $validatedData['twitter_user'],
            [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . config('ew.ewdb.token'),
                ],
            ]
        );

        $hasRecent = false;
        $redirectTo = '';
        $twitterLink = '';

        if ($response->getStatusCode() === 200) {
            $recordId = intval($response->getBody()->getContents());

            if ($recordId > 0) {
                $hasRecent  = true;
                $redirectTo = route('twitter.result', ['id' => $recordId]);
            }
        }

        if (!$hasRecent) {
            if (!empty(session('twitter_oauth_token'))) {
                $this->clearSessionVars(true);
            }

            $twitter      = resolve('Abraham\TwitterOAuth\TwitterOAuth');
            $requestToken = $twitter->oauth('oauth/request_token', ['oauth_callback' => 'oob']);
            $resultCode   = $twitter->getLastHttpCode();

            if ($resultCode == 200) {
                session([
                    'twitter_request_ident'      => $requestToken['oauth_token'],
                    'twitter_oauth_token'        => $requestToken['oauth_token'],
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
            'hasRecent'   => $hasRecent,
            'twitterLink' => $twitterLink,
            'redirectTo'  => $redirectTo,
        ]);
    }

    /**
     *
     */
    public function vueCheckPin(Request $request)
    {
        $validatedInput = $request->validate([
            'twitter_user' => 'required|string|alpha_dash',
            'pin_number'   => 'required|integer',
            'email'        => 'required|email',
        ]);

        $twitter     = resolve('Abraham\TwitterOAuth\TwitterOAuth');
        $accessToken = $twitter->oauth('oauth/access_token',
                                        ['oauth_verifier' => $validatedInput['pin_number']]);
        $resultCode  = $twitter->getLastHttpCode();

        if ($resultCode != 200) {
            return response()->json([
                'errors' => ['Unable to verify PIN.']
            ], 500);
        }

        session(['twitter_access_token' => $accessToken]);

        $twitterRequest = TwitterRequest::make([
            'twitter_username'  => $validatedInput['twitter_user'],
            'email'             => $validatedInput['email'],
            'request_ident'     => session('twitter_request_ident'),
            'access_token'      => json_encode($accessToken),
        ]);

        if (!$twitterRequest->save()) {
            return response()->json([
                'errors' => ['Unable to save request.'],
            ], 500);
        }

        $this->clearSessionVars();

        ForwardTwitterRequest::dispatch($twitterRequest);

        return response()->json([
            'redirectTo' => route('twitter.done', ['twitterRequest' => $twitterRequest]),
        ]);
    }

    /**
     *
     */
    public function requestProcessed(RequestIdRequest $request)
    {
        $requestId      = $request->input('request_id');
        $twitterRequest = TwitterRequest::fromRequestId($requestId);

        Mail::to($twitterRequest->email)->send(new TwitterRequestProcessed($twitterRequest));

        return response()->json('All done!', 200);
    }

    /**
     *
     */
    private function clearSessionVars($all = false)
    {
        session()->forget(['twitter_oauth_token', 'twitter_oauth_token_secret']);

        if ($all) {
            session()->forget(['twitter_access_token', 'twitter_request_ident']);
        }
    }
}
