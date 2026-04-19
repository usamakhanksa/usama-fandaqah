<?php

namespace App\Http\Controllers;
use App\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\PhoneVerificationCode;

class SmsVerificationController extends Controller
{
    public function verifySmsCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'verification_code' => 'required|numeric|digits:4',
        ]);

        // Find the verification record for the user
        $verification = PhoneVerificationCode::where('user_id', $request->user_id)
                                            ->where('verification_code', $request->verification_code)
                                            ->first();

        if (!$verification) {
            return response()->json(['status' => false, 'message' => 'Invalid verification code']);
        }

        // Check if the code has expired
        if (Carbon::now()->gt($verification->expires_at)) {
            return response()->json(['status' => false, 'message' => 'Verification code has expired']);
        }

        // Mark the phone as verified (you can add a field like `is_phone_verified` in the User model)
        $user = User::find($request->user_id);
        $user->phone_verified_at = now();
        $user->save();

        // Delete the verification code after use
        $verification->delete();

        return response()->json(['status' => true, 'message' => 'Phone number verified successfully']);
    }
}
