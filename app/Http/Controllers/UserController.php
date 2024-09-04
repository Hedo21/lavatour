<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
/** @var \App\Models\User $user **/

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ],
            [
                'name.required'     => 'name is empty',
                'email.required'    => 'email is empty',
                'password.required' => 'password is empty',
            ]
        );
        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => $validator->errors(),
                ]
            );
        } else {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
            if ($user) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'status'       => 'success',
                    'access_token' => $token,
                    'message'      => 'registration success',
                ], 200);
            } else {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'registration failed',
                ], 401);
            }
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email'    => 'required',
                'password' => 'required',
            ],
            [
                'email.required'    => 'email is empty',
                'password.required' => 'password is empty',
            ]
        );
        if ($validator->fails()) {
            return response(
                [
                    'status'  => 'failed',
                    'message' => $validator->errors()->all(),
                ],
                422
            );
        }
        $user  = User::where('email', '=', $request->input('email'))->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('auth_token')->plainTextToken;
                $response = [
                    'status'       => 'success',
                    'message'      => 'login success',
                    'access_token' => $token,
                    'token_type'   => 'bearer',
                ];
                return response($response, 200);
            } else {
                $response = [
                    'stutus'  => 'failed',
                    'message' => 'incorrect password',
                ];
                return response($response, 422);
            }
        } else {
            $response = [
                'status'  => 'failed',
                'message' => 'wrong email or unregistered email'
            ];
            return response($response, 422);
        }
    }

    public function getprofile(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'data is successfully displayed',
                'data'    => auth()->user(),
            ]);
        } else {
            return response()->json([
                'status'  => 'failed',
                'message' => 'User not logged in',
            ], 401);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'     => 'required|string|max:255',
                'password' => 'required|string|min:8',
            ],
            [
                'name.required'     => 'name is empty',
                'password.required' => 'password is empty'
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'failed',
                'message' => $validator->errors(),
            ], 401);
        } else {
            $user = Auth::user();
            $user->name = $request->name;
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'status'  => 'success',
                'id'      => $user->id,
                'data'    => $user,
                'message' => 'update successfully',
            ], 200);
        }
    }

    public function someMethod()
    {
        // $user = Auth::user();
        // Log::info('Authenticated User:', ['user' => $user]);
        // return [
        //     'data' => $user,
        // ];

        // return [
        //     'data' => Auth::user(),
        // ];

        // dd(Auth::user());

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Unauthorized',
            ], 401);
        }
        return response()->json([
            'user' => $user
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return [
            'status'  => 'success',
            'message' => 'log out success'
        ];
    }
}
