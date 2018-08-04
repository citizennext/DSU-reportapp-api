<?php

namespace App\Providers;

use App\User;
use App\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        

        $this->app['auth']->viaRequest('apikey', function ($request) {
            if ($request->header('Authorization')) {
                $apiKey = explode(' ',$request->header('Authorization'));
                $userModel = User::where(['api_key' => decrypt($apiKey[1]), 'active' => 1])->first();
                if(!empty($userModel)){
                    // check expired token
                    $expireApiKeyTime = Setting::where('key', 'api.api_key_expire')->first()->value;
                    $diffExpire = floor(Carbon::now()->diffInMinutes(Carbon::parse($userModel->api_key_expire)));
                    if($diffExpire > $expireApiKeyTime){
                        $userModel = null;
                    } else {
                        $request->request->add(['id' => $userModel->id]);
                    }
                }
                return $userModel;
            }
        });
    }
}
