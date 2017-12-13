<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Abraham\TwitterOAuth\TwitterOAuth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('base64', function ($attribute, $value, $parameters, $validator) {
            if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $value)) {
                return true;
            } else {
                return false;
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Abraham\TwitterOAuth\TwitterOAuth', function ($app) {
            $userToken = session('twitter_oauth_token', null);
            $userTokenSecret = session('twitter_oauth_token_secret', null);
            $consumerKey = config('services.twitter.key', null);
            $consumerSecret = config('services.twitter.secret', null);

            if (!empty($userToken) && !empty($userTokenSecret)) {
                return new TwitterOAuth($consumerKey, $consumerSecret,
                                        $userToken, $userTokenSecret);
            } else {
                return new TwitterOAuth($consumerKey, $consumerSecret);
            }
        });
    }
}
