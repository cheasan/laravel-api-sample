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
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function login(LoginUserRequest $request)
    {

        $validated = $request->validated();
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return new ErrorResponse(
                null,
                ['message' => 'Email or password might be incorrect. Try again.'],
                401
            );
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return new ErrorResponse(
                null,
                ['message' => 'Email or password might be incorrect. Try again.'],
                401
            );
        }

        return new SuccessResponse(
            ['token' => $user->createToken("API TOKEN")->plainTextToken],
            'User Created Successfully',
            200
        );
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

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            return new ErrorResponse(['message' => $e->getMessage()]);
        }

        return new SuccessResponse(
            ['token' => $user->createToken("API TOKEN")->plainTextToken],
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
