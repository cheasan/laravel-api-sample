<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\ResetLoggedInPasswordRequest;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{

    protected UserRepository $userRepository;
    protected CustomerRepository $customerRepository;

    public function __construct(UserRepository $userRepository, CustomerRepository $customerRepository)
    {
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepository;
    }

    public function login(LoginUserRequest $request): ErrorResponse|SuccessResponse
    {

        $validated = $request->validated();
        $user = $this->userRepository->find('email', $validated['email']);

        if (!$user) {
            return new ErrorResponse(
                ['message' => 'Email or password might be incorrect. Try again.'],
                401
            );
        }

        if (!Hash::check($validated['password'], $user->password)) {
            return new ErrorResponse(['message' => 'Email or password might be incorrect. Try again.'], 401);
        }

        // Check if the user has already verified their email
        if (!$user->hasVerifiedEmail()) {
            return new ErrorResponse(['message' => 'Please verify your email before login.']);
        }

        return new SuccessResponse(
            'User login successfully',
            ['token' => $user->createToken("API TOKEN")->plainTextToken]
        );
    }

    public function createUser(CreateUserRequest $request): ErrorResponse|SuccessResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {

            $user = $this->userRepository->create([
                'email' => $validated['email'],
                'password' => bcrypt($validated['password'])
            ]);

            $this->customerRepository->create([
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
            'User Created Successfully',
            ['token' => $user->createToken("API TOKEN")->plainTextToken],
            201
        );
    }

    public function logoutUser(): SuccessResponse
    {
        request()->user()->currentAccessToken()->delete();

        return new SuccessResponse('User logout successfully.');
    }

    public function verifyRegisteredEmail(HttpRequest $request): ErrorResponse|SuccessResponse
    {
        $user = $this->userRepository->findByIdOrFail($request->route('id'));

        if (!URL::hasValidSignature($request)) {
            return new ErrorResponse(['message' => 'Invalid verification link.']);
        }

        if ($user->hasVerifiedEmail()) {
            return new ErrorResponse(['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return new SuccessResponse('Email verified successfully.');
    }

    public function resetLoggedInPassword(ResetLoggedInPasswordRequest $request): SuccessResponse
    {
        $validated = $request->validated();
        $user = $this->userRepository->findById(request()->user()->id);

        if (!$user) {
            return new ErrorResponse(
                ['message' => 'Something went wrong with the token. User not found. Try logout and login again.'],
                401
            );
        }

        // verify current password
        if (!Hash::check($validated['password'], $user->password)) {
            return new ErrorResponse(['message' => 'Current password is incorrect. Try again.'], 401);
        }

        // set new password
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        // send an email alert

        return new SuccessResponse('Password reset successfully.');

    }
}
