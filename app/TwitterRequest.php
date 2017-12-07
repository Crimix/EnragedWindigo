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
}
