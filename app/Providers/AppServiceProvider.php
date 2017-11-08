<?php

namespace App\Providers;

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
        //
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
                                        $userToken, $userToken);
            } else {
                return new TwitterOAuth($consumerKey, $consumerSecret);
            }
        });
    }
}
