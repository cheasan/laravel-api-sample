<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPassword\ForgotPasswordRequest;
use App\Http\Requests\ForgotPassword\ResetPasswordRequest;
use App\Http\Requests\ForgotPassword\VerifyPinRequest;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Mail\ForgotPassword;
use App\Repositories\UserRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{

    protected string $resetTokenTable = 'password_reset_tokens';
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws \Exception
     */
    public function forgotPassword(ForgotPasswordRequest $request): ErrorResponse|SuccessResponse
    {

        $validated = $request->validated();
        $verify = $this->userRepository->find('email', $validated['email'])->exists();

        if (!$verify) {
            return new ErrorResponse(['message' => 'Email already verified.']);
        }

        $existingResetToken =  DB::table($this->resetTokenTable)->where([
            ['email', $request->all()['email']]
        ]);

        if ($existingResetToken->exists()) {
            $existingResetToken->delete();
        }

        $token = random_int(100000, 999999);

        $password_reset = DB::table($this->resetTokenTable)->insert([
            'email' => $validated['email'],
            'token' =>  bcrypt($token),
            'created_at' => Carbon::now()
        ]);

        if ($password_reset) {
            Mail::to($request->all()['email'])->send(new ForgotPassword($token));
            return new SuccessResponse('Password reset Email has been sent to user.');
        }

        return new ErrorResponse(['message' => 'Password reset token could not generate successfully.']);
    }

    public function verifyPin(VerifyPinRequest $request): ErrorResponse|SuccessResponse
    {

        $validated = $request->validated();

        $resetToken = DB::table($this->resetTokenTable)->where(
            'email',
            $validated['email']
        );

        if (!$resetToken->exists()) {
            return new ErrorResponse(['message' => 'Provided token is not correct.'], 401);
        }

        if (!Hash::check($validated['token'], $resetToken->first()->token)) {
            return new ErrorResponse(['message' => 'Provided token is not correct.'], 401);
        }

        $difference = Carbon::now()->diffInSeconds($resetToken->first()->created_at);

        if ($difference > 3600) {
            return new ErrorResponse(['message' => 'Token Expired.']);
        }

        return new SuccessResponse(
            'Token verified.',
            ['resetPasswordToken' => $resetToken->first()->token]
        );
    }

    public function resetPassword(ResetPasswordRequest $request): ErrorResponse|SuccessResponse
    {
        $validated = $request->validated();

        $resetToken = DB::table($this->resetTokenTable)->where(
            'token',
            $validated['token'],
        );

        if (!$resetToken->first()) {
            return new ErrorResponse(['message' => 'Token Incorrect']);
        }

        $user = $this->userRepository->find('email', $resetToken->first()->email);

        DB::beginTransaction();

        try {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);

            $resetToken->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return new ErrorResponse(['message' => $e->getMessage()]);
        }

        // send password reset successful email

        return new SuccessResponse(
            'User password reset successfully.',
            ['token' => $user->createToken("API TOKEN")->plainTextToken]
        );
    }
}
