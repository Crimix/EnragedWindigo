<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwitterRequest extends Model
{
    protected $fillable = [
        'email', 'request_ident', 'access_token',
    ];
}
