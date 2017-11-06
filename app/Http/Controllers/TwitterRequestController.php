<?php

namespace App\Http\Controllers;

use App\TwitterRequest;
use Illuminate\Http\Request;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterRequestController extends Controller
{
    /**
     * 
     */
    public function init(TwitterOAuth $twitter) {
        // TODO: Request token with callback url (oauth/request_token)
        // TODO: Create unique ID and store in session
        // TODO: Store token vars in session
        // TODO: Fetch authorize url (oauth/authorize)
        // TODO: Redirect
    }

    /**
     * 
     */
    public function callbackHandler() {
        // TODO: Check if request was denied and redirect if so.
        // TODO: Retrieve tokens from session.
        // TODO: If tokens are incorrect, remove the session vars and redirect.
        // TODO: Instantiate TwitterOAuth object with user auth params
        // TODO: Request access token (oauth/access_token) using the 'oauth_verifier' request param
        // TODO: If succeeded, remove the token and token secret, and store the access token
        // TODO: Redirect to request creation form
    }

    /**
     * 
     */
    public function create() {
        // TODO: Require access token presence
        // TODO: Display form

        return view('twitter.create');
    }

    /**
     * 
     */
    public function store(Request $request) {
        // TODO: Require access token presence
        // TODO: Validate input
        // TODO: Create and store TwitterRequest
        // TODO: Add sending-task to local queue
        // TODO: Redirect to verification page
    }

    /**
     * 
     */
    public function done(TwitterRequest $twitterRequest) {
        // TODO: Display the details of the request

        return view('twitter.done', ['twitterRequest' => $twitterRequest]);
    }

    /**
     * 
     */
    public function missingAuth() {
        return view('twitter.missingAuth');
    }
}
