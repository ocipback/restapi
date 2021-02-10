<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function getresponse(User $user){
        $tokenauth = $user->createToken('Personal Access Token');
        $token = $tokenauth->token;
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'success' => true,
            'accessToken' => $tokenauth->accessToken,
            'tokenType' => 'Bearer',
            'expiredToken' => Carbon::parse($token->expires_at)->toDateTimeString(),
            'message' => 'authorize'
        ], 200);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(),[
            'username' => 'required|max:255',
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);
        if ($validator->fails()){
           return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
               'message' => 'invalid to input'
            ],422);
        }

        $user = User::create([
           'username' => $request->username,
           'name' => $request->name,
           'email' => $request->email,
           'password' => bcrypt($request->password)
        ]);

        return $this->getresponse($user);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(),[
           'email' => 'required',
           'password' => 'required'
        ]);

        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'invalid email dan password'
            ],422);
        }

        $credentials = \request(['email', 'password']);

        if (Auth::attempt($credentials)) {
            $user = $request->user();
            return $this->getResponse($user);
        } else {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'invalid credentials make sure email and password'
            ]);
        }

    }

    public function logout(Request $request){
   $check =  $request->user()->token()->revoke();
    if (!$check){
        return response()->json([
            'success' => false,
            'message' => 'Unsuccessfully to logged out'
        ]);
    }
    return response()->json([
        'success' => true,
        'message' => 'Successfully to logged out'
    ]);
    }

    public function me(Request $request){
        return $request->user();
    }

    public function updateprofile(Request $request,User $user)
    {

    }

}
