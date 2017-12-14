<?php

namespace App\Http\Controllers;

use Validator;
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
     *
     */
    public function show(Request $request, DataProcessor $processor)
    {
        $validator = Validator::make($request->all(), [
            'request_id'    => 'required_without:record_id|base64|min:10',
            'record_id'     => 'required_without:request_id|integer|min:1',
            'twitter_user'  => 'required_with:record_id|string|alpha_dash|max:20',
        ]);

        if ($validator->fails()) {
            return redirect('/')->withErrors($validator);
        }

        $result         = null;
        $requestId      = $request->input('request_id');
        $recordId       = $request->input('record_id');
        $twitterName    = $request->input('twitter_user', '');
        $guzzle         = new GuzzleClient([
            'base_uri'    => config('ew.ewdb.url'),
            'http_errors' => false,
        ]);

        if (!empty($requestId)) {
            $twitterRequest = TwitterRequest::fromRequestId($requestId);

            // Retrieve record ID from DB server
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
                $recordId    = intval($response->getBody()->getContents());
                $twitterName = $twitterRequest->twitter_username;
            }
        }

        if (!empty($recordId) && $recordId > 0) {
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

        if (empty($result) || (strtolower($twitterName)
                                != strtolower($result['user']['twitter_name']))) {
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
        $titleFontSize  = 16;
        $chartSize      = ['width' => 400, 'height' => 200];

        $barOptions = [
            'legend' => [
                'display' => false,
            ],
            'scales' => [
                'yAxes' => [[
                    'scaleLabel' => [
                        'display' => true,
                        'labelString' => 'Percentage',
                    ],
                    'ticks' => [
                        'beginAtZero' => true,
                    ],
                ]],
                'xAxes' => [[
                    'categoryPercentage' => 1.0,
                ]],
            ],
        ];

        $scatterOptions = [
            'legend' => [
                'display' => false,
            ],
            'scales' => [
                'xAxes' => [[
                    'scaleLabel' => [
                        'display' => true,
                        'labelString' => 'Political',
                    ],
                    'ticks' => [
                        'min' => -10,
                        'max' => 10,
                    ],
                ]],
                'yAxes' => [[
                    'scaleLabel' => [
                        'display' => true,
                        'labelString' => 'Sentiment',
                    ],
                    'ticks' => [
                        'min' => -10,
                        'max' => 10,
                    ],
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
                                ->size($chartSize)
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
                            ->size($chartSize)
                            ->labels($data['labels'])
                            ->datasets($data['datasets'])
                            ->optionsRaw($barOptions);

        /* --------
         * MI - Bar
         * --------
         */
        $data = $processor->getChartJsBarData('mi', 10);
        $miBar = app()->chartjs
                        ->name('miBar')
                        ->type('bar')
                        ->size($chartSize)
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
                                ->size($chartSize)
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
                            ->size($chartSize)
                            ->labels($data['labels'])
                            ->datasets($data['datasets'])
                            ->optionsRaw($barOptions);

        return view('twitter.show')
                ->with([
                    'twitterName'           => $twitterName,
                    'userCount'             => $processor->getUserCount(),
                    'tweetCounts'           => $processor->getTweetCounts(),
                    'analysisChartScatter'  => $analysisScatter,
                    'analysisChartBar'      => $analysisBar,
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
    public function vueCheck(Request $request)
    {
        $validatedData = $request->validate([
            'twitter_user' => 'required|string|alpha_dash|max:20',
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
                $redirectTo = route('twitter.result', [
                    'record_id' => $recordId,
                    'twitter_user' => $validatedData['twitter_user']
                ]);
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
            'twitter_user' => 'required|string|alpha_dash|max:20',
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
