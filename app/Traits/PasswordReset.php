<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 *
 */
Trait PasswordReset
{
    public function changePassword(Request $request, $Model,){
        $this->validate($request,[
            'password'=> 'required|confirmed'

        ]);

        $email = $request->email;

        $admin = $Model::where('email', $email)->first();

        if($admin !== NULL){
            $admin->password = Hash::make($request->password);
            return $this->sendResponse($admin, 'Password reset successful.');
        }

        return $this->sendError('No record found for this user', 'Invalid user identifier');

    }
}

