<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

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

        // Check if the user has already verified their email
        if (!$user->hasVerifiedEmail()) {
            return new ErrorResponse(
                null,
                ['message' => 'Please verify your email before login.'],
                400
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

            // Send the email verification notification
            $user->notify(new VerifyEmail);

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

    public function verify(HttpRequest $request)
    {
        // Find the user by id
        $user = User::findOrFail($request->route('id'));

        // Check if the url is a valid signed url
        if (!URL::hasValidSignature($request)) {
            return response()->json(['error' => 'Invalid verification link.']);
        }

        // Check if the user has already verified their email
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        // Mark the user as verified and fire the event
        $user->markEmailAsVerified();
        event(new Verified($user));

        // Return a success response
        return response()->json(['message' => 'Email verified successfully.']);
    }
}
