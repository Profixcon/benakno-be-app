<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->error($request->validator->errors(), 400);
        }

        $user = auth("api")->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->error('Old password is incorrect', 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->success(null, 200, 'Password updated successfully');
    }
}
