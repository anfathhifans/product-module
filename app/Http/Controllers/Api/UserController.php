<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\{Auth,Hash,Response};

class UserController extends Controller
{

    /**
     * register API
     *
     * @return void
     */
    public function register(Request $request)
    {
        // validation
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        // create data
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // send response
        return Response::json([
            'status' => 1,
            'message' => 'User registered successfully',
        ]);
    }

    /**
     * login API
     *
     * @return void
     */
    public function login(Request $request)
    {
        // validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // check users
        if(auth()->attempt([
            'email' => $request->email,
            'password' => $request->password
        ])){
            if(auth()->user()->email_verified_at == NULL){
                Auth::logout();

                return Response::json([
                    'status' => 0,
                    'message' => 'User email not verified',
                ], 404);

            }else{

                $token = $request->user()->createToken('auth_token');

                return Response::json([
                    'status' => 1,
                    'message' => 'User logged in successfully',
                    'token' => $token->plainTextToken
                ]);

            }
        }else{
            return Response::json([
                'status' => 0,
                'message' => 'Invalid Credentials',
            ], 404);
        }
    }

    /**
     * profile API
     *
     * @return void
     */
    public function profile()
    {
        return Response::json([
            'status' => 1,
            'message' => 'User Profile Information',
            'data' => auth()->user()
        ]);
    }

    /**
     * logout API
     *
     * @return void
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return Response::json([
            'status' => 1,
            'message' => 'User Logged out successfully',
        ]);

        // Auth::logout();
        // return redirect('/');

    }
}
