<?php

use App\TwitterRequest;
use Illuminate\Database\Seeder;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('twitter_requests')->delete();

        $twitterRequest = TwitterRequest::make([
            'email' => 'receiver@somecompanyname.io',
            'request_ident' => uniqid('', true),
            'twitter_username' => 'theDonaldDrumpf',
            'access_token' => '{"oauth_token":"14594669-CUFTDY6oMmC6FIu3IwHY65G26cQHmmuc9XhHuQFaL","oauth_token_secret":"uzV2suIyfoTaHDgbZERCsxBlqjMaFFCfGJ0yNDbra5b5K","user_id":"14594669","screen_name":"angelod1981","x_auth_expires":"0"}',
        ]);

        $twitterRequest->save();
    }
}
