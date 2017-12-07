<?php

namespace App\Jobs;

use App\TwitterRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client as GuzzleClient;

class ForwardTwitterRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public     $tries = 10;
    protected  $twitterRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TwitterRequest $twitterRequest)
    {
        $this->twitterRequest = $twitterRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->twitterRequest)) {
            return;
        }

        $requestId   =
            base64_encode($this->twitterRequest->id . ':' . $this->twitterRequest->request_ident . ':' . $this->twitterRequest->twitter_username);
        $accessToken = json_decode($this->twitterRequest->access_token, true);
        $guzzle      = new GuzzleClient([
            'base_uri'    => config('ew.queue.url'),
            'http_errors' => false,
        ]);
        $response    = $guzzle->request(
            'POST',
            '/api/AnalyzeTwitterAccount',
            [
                'form_params' => [
                    'Name'          => $this->twitterRequest->twitter_username,
                    'Token'         => $accessToken['oauth_token'],
                    'Secret'        => $accessToken['oauth_token_secret'],
                    'RequestID'     => $requestId,
                ],
                'headers'     => [
                    'Accept'        => 'application/json',
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Error Processing Request", $response->getStatusCode());
        }
    }
}
