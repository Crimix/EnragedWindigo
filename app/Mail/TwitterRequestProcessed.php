<?php

namespace App\Mail;

use App\TwitterRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TwitterRequestProcessed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $twitterRequest;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TwitterRequest $twitterRequest)
    {
        $this->twitterRequest = $twitterRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.twitter.processed')
                    ->with([
                        'twitter_username' => $this->twitterRequest->twitter_username,
                        'requestId' => $this->twitterRequest->requestId,
                    ]);
    }
}
