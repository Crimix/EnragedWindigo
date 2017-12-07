<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwitterRequest extends Model
{
    protected $fillable = [
        'email', 'request_ident', 'access_token', 'twitter_username',
    ];

    public function getRequestIdAttribute()
    {
        return base64_encode(
                sprintf('%d:%s:%s',
                        $this->id,
                        $this->request_ident,
                        $this->twitter_username)
        );
    }

    public static function fromRequestId($requestId)
    {
        if (empty($requestId)) {
            return null;
        }

        $requestInfo    = explode(':', base64_decode($requestId));
        $twitterRequest = TwitterRequest::where('id', $requestInfo[0])
                            ->where('request_ident', $requestInfo[1])
                            ->where('twitter_username', $requestInfo[2])
                            ->first();

        return $twitterRequest;
    }
}
