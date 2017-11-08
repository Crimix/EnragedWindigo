<?php

namespace App\Jobs;

use App\TwitterRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ForwardTwitterRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $twitterRequest;

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
        // TODO: Send request to server.
    }
}
