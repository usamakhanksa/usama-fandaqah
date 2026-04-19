<?php
namespace App\Http\Controllers\APi\Auth;

use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\PasswordReset;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{

    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user)
            throw new ValidationException(__("We can't find a user with that e-mail address."));

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => str_random(60)
             ]
        );
        if ($user && $passwordReset)
            // $user->notify(
            //     new PasswordResetRequest($passwordReset->token)
            // );
            $user->notify(new ResetPassword($passwordReset->token));
        throw new ValidationException(__('We have e-mailed your password reset link!'));
    }

    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    // public function find($token)
    // {
    //     $passwordReset = PasswordReset::where('token', $token)
    //         ->first();
    //     if (!$passwordReset)
    //         throw new ValidationException(__('This password reset token is invalid.'));

    //     if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
    //         $passwordReset->delete();
    //         throw new ValidationException(__('This password reset token is invalid.'));
    //     }
    //     return response()->json($passwordReset);
    // }

     /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    // public function reset(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|string|email',
    //         'password' => 'required|string|confirmed',
    //         'token' => 'required|string'
    //     ]);

    //     if ($validator->fails())
    //         throw new ValidationException($validator->errors()->first());

    //     $passwordReset = PasswordReset::where([
    //         ['token', $request->token],
    //         ['email', $request->email]
    //     ])->first();
    //     if (!$passwordReset)
    //         throw new ValidationException(__('This password reset token is invalid.'));        
    //     $user = User::where('email', $passwordReset->email)->first();
    //     if (!$user)
    //         throw new ValidationException(__("We can't find a user with that e-mail address."));  
 
    //     $user->password = bcrypt($request->password);
    //     $user->save();
    //     $passwordReset->delete();
    //     $user->notify(new PasswordResetSuccess($passwordReset));
    //     return response()->json($user);
    // }
}