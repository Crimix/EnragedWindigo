<?php
namespace App\Http\Middleware;

use Closure;

class TwitterToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $accessToken = session('twitter_access_token', '');
        $validToken = (strpos('oauth_token=', $accessToken) !== false) && (strpos('oauth_token_secret=', $accessToken) !== false);

        if (empty($accessToken) || !$validToken) {
            return redirect()->route('twitter.missingAuth');
        }

        return $next($request);
    }
}
