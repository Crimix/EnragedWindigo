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
        $validToken = is_array($accessToken) && !empty($accessToken['oauth_token']) && !empty($accessToken['oauth_token_secret']);

        if (!$validToken) {
            return redirect()->route('twitter.missingAuth');
        }

        return $next($request);
    }
}
