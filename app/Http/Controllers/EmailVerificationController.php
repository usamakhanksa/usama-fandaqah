<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Validate the hash
        $expectedHash = hash('sha256', $user->email);
        if ($hash !== $expectedHash) {
            return redirect('/email-verified-success')->with('success', 'Invalid verification link.');
        }

        if ($user->email_verified_at) {
            return redirect('/email-verified-success')->with('success', 'Email already verified.');
        }

        $user->email_verified_at = now();
        $user->save();

        return redirect('/email-verified-success')->with('success', 'Email verified successfully!');
    }
    public function success()
    {
        $message = session('success'); // Retrieve the success message if needed
        return view('email_verified_success', compact('message'));
    }

}
