<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class SocialateController extends Controller
{
    //
    public function google(){
        // dd('Ola');
        return Socialite::driver('google')->redirect(); 
        // dd(Socialite::driver('google')->redirect());       
    }
    public function callbackGoogle() {
        // dd('Ola');
        $user = Socialite::driver('google')->user();
        dd($user);
        return response()->json($user);
        
    }
    public function facebook(){
        // dd(Socialite::driver('facebook')->redirect());
        return Socialite::driver('facebook')->redirect(); 
    }
}
