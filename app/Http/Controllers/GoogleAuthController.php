<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $redirect_url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        Log::debug("redirect google auth");
        return response()->json($redirect_url, 200);
    }

    public function handleGoogleCallback()
    {
        try {
            $socialiteUser = Socialite::driver('google')->stateless()->user();
            $email = $socialiteUser->email;
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $socialiteUser->name,
                'email' => $socialiteUser->email,
                'password' => Str::random(8)
            ]);

            Auth::login($user);

            return response()->json([
                'user' => $user,
                'access_token' => $user->createToken('google-token')->plainTextToken,
                'token_type' => 'Bearer',
            ]);
        } catch (Exception $e) {
            Log::error($e);
            return response(['error' => 'Unauthorized to Fail Google Login'] , 401);
        }
    }
}
