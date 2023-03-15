<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;

class AuthController extends Controller
{
    public function login(LoginUserRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = User::where('email', $request->email)->first();

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function createUser(CreateUserRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {

            $user = User::create([
                'email' => $validated['email'],
                'password' => bcrypt($validated['password'])
            ]);

            Customer::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
            ]);

            $token = $user->createToken("API TOKEN")->plainTextToken;

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            return new ErrorResponse(['message' => $e->getMessage()]);
        }

        return new SuccessResponse(
            ['token' => $token],
            'User Created Successfully',
            201
        );
    }

    public function logoutUser()
    {
        request()->user()->currentAccessToken()->delete();

        return new SuccessResponse(
            null,
            'User logout successfully.'
        );
    }
}
