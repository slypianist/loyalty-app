<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\BaseController;

class PasswordResetController extends BaseController
{
    public function sendResetLinkPartner(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user) {
            $this->sendError('User not found.');

        }

        $token = Str::uuid()->toString();
        $this->saveToken($user->email, $token, 'password_resets');

        $resetLink = env('FRONTEND_URL') . '/partner-password-reset?token=' . $token;
        //dd($resetLink);

        Mail::send('emails.password_reset', ['resetLink' => $resetLink], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Reset');
        });

       return $this->sendResponse('Password reset link sent successfully', 'Successful');
    }

   // Password reset for Rep.
    public function sendResetLinkRep(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $rep = DB::table('reps')->where('email', $request->email)->first();

        if (!$rep) {
            $this->sendError('User not found.');
        }

        $token = Str::uuid()->toString();
        $this->saveToken($rep->email, $token, 'rep_password_resets');

        $resetLink = env('FRONTEND_URL') . '/rep-password-reset?token=' . $token;

        Mail::send('emails.password_reset', ['resetLink' => $resetLink], function ($message) use ($rep) {
            $message->to($rep->email)
                ->subject('Password Reset');
        });

        return $this->sendResponse('Password reset link sent successfully.', 'Successful');

    }

    // Password Reset for admin.

    public function sendResetLinkAdmin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $admin = DB::table('admins')->where('email', $request->email)->first();

        if (!$admin) {
            $this->sendError('User not found.');

        }

        $token = Str::uuid()->toString();
        $this->saveToken($admin->email, $token, 'admin_password_resets');

        $resetLink = env('FRONTEND_URL') . '/admin-password-reset?token=' . $token;

        Mail::send('emails.password_reset', ['resetLink' => $resetLink], function ($message) use ($admin) {
            $message->to($admin->email)
                ->subject('Password Reset');
        });

        return response()->json(['message' => 'Password reset link sent successfully']);
    }

    private function saveToken($email, $token, $tableName)
    {
        DB::table($tableName)->updateOrInsert(
            ['email' => $email],
            ['email' => $email, 'token' => $token, 'created_at' =>  \Carbon\Carbon::now()]
        );
    }


    public function resetPartnerPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return $this->sendError('Invalid link');
        }

        // Check if the token has expired (5 minutes in this example)
        $tokenExpiration = Carbon::parse($passwordReset->created_at)->addMinutes(5);
        if (Carbon::now()->gt($tokenExpiration)) {
            // Token expired
            return $this->sendError('Link has expired. Try again');
        }

        DB::table('users')
            ->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return $this->sendResponse('Password reset successfully.', 'successful.');

    }

    //Reset admin password

    public function resetAdminPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordReset = DB::table('admin_password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return $this->sendError('Invalid link');

        }

        // Check if the token has expired (5 minutes in this example)
        $tokenExpiration = Carbon::parse($passwordReset->created_at)->addMinutes(5);
        if (Carbon::now()->gt($tokenExpiration)) {
            // Token expired
            return $this->sendError('Link has expired. Try again');
        }

        DB::table('admins')
            ->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('admin_password_resets')->where('email', $request->email)->delete();

        return $this->sendResponse('Password reset successfully.', 'successful.');

    }

    public function resetRepPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $passwordReset = DB::table('rep_password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return $this->sendError('Invalid Token');

        }

        // Check if the token has expired (5 minutes in this example)
        $tokenExpiration = Carbon::parse($passwordReset->created_at)->addMinutes(5);
        if (Carbon::now()->gt($tokenExpiration)) {
            // Token expired
            return $this->sendError('Link has expired. Initiate a new password reset.');

        }

        DB::table('reps')
            ->where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        return $this->sendResponse('Password reset successfully.', 'successful.');

    }


}
