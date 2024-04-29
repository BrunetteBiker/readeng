<?php

namespace App\Http\Controllers;

use App\Enum\UserStatus as UserStatusEnum;
use App\Enum\UserRole as UserRoleEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    function login(Request $request)
    {
        $email = $request->get("email");
        $password = $request->get("password");

        $user = User::where("email", $email)->first();

        if (is_null($user)) {
            return [
                "type" => "warning",
                "msg" => "Istifadeci tapilmadi"
            ];
        }

        if ($user->status_id == UserStatusEnum::BLOCKED->value) {
            return [
                "type" => "danger",
                "msg" => "Istifadeci fealiyyet mehdudlasdirilmisdir"
            ];
        }

        if ($user->status_id == UserStatusEnum::UNVERIFIED->value) {

            $user->verify_token = generateToken($user->id);
            $user->save();

            return [
                "type" => "info"
            ];

        }

        if (!Hash::check($password, $user->password)) {
            return [
                "type" => "danger",
                "msg" => "Sifre duzgun deyil"
            ];
        }

        $user->status_id = UserStatusEnum::ONLINE->value;
        $user->last_login = now()->getTimestamp();
        $user->save();

        return [
            "type" => "success",
            "msg" => "Giris ugurlu oldu"
        ];

    }

    function registration(Request $request)
    {
        $fullname = $request->get("fullname");
        $email = $request->get("email");
        $password = $request->get("password");
        $rePassword = $request->get("re-password");

        $validator = Validator::make([
            "fullname" => $fullname,
            "email" => $email,
            "password" => $password,
            "re-password" => $rePassword,
        ], [
            "fullname" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|min:6",
            "re-password" => "required|same:password",
        ], [
            "fullname.required" => "Ad ve soyad daxil edilmelidir",
            "email.required" => "E-poct unvani daxil edilmelidir",
            "email.email" => "E-poct unvani duzgun deyil",
            "email.unique" => "E-poct unvani istifade olunmusdur",
            "password.required" => "Yeni sifre daxil edilmelidir",
            "password.min" => "Yeni sifre minimum alti (6) simvoldan ibaret olmalidir",
            "re-password.required" => "Tekrar sifre daxil edilmelidir",
            "re-password.same" => "Tekrar sifre ve yeni sifre eyni olmalidir",
        ]);


        if ($validator->fails()) {
            return [
                "type" => "warning",
                "msg" => $validator->errors()->first()
            ];
        }


        $user = new User();
        $user->fullname = $fullname;
        $user->email = $email;
        $user->role_id = UserRoleEnum::CUSTOMER->value;
        $user->status_id = UserStatusEnum::UNVERIFIED->value;
        $user->password = Hash::make($password);
        $user->save();

        $user->nickname = "user" . now()->getTimestamp() . Str::of($user->id)->padLeft(6, 0);
        $user->pin = "REU-" . Str::of($user->id)->padLeft(6, 0);
        $user->verify_token = generateToken($user->id);

        $user->save();

        return [
            "type" => "success",
            "msg" => "Qeydiyyat ugurla basa catdi"
        ];
    }

    function forgotPassword(Request $request)
    {
        $email = $request->get("email");
        $user = User::where("email", $email)->first();

        if (is_null($user)) {
            return [
                "type" => "warning",
                "msg" => "Istifadeci tapilmadi"
            ];
        }

        if ($user->status_id == UserStatusEnum::BLOCKED->value) {
            return [
                "type" => "danger",
                "msg" => "Istifadeci fealiyyet mehdudlasdirilmisdir"
            ];
        }

        if ($user->role_id != UserRoleEnum::CUSTOMER->value) {
            return [
                "type" => "warning",
                "msg" => "Bu sorgu icra oluna bilmez.Inzibatci ile elaqe saxlayin"
            ];
        }

        $user->reset_token = generateToken($user->id);
        $user->save();

        return [
            "type" => "success",
            "msg" => "Sifre berpasi ucun melumatlar e-poct unvaniniza gonderildi"
        ];


    }

    function resetPassword($resetToken)
    {
        $user = User::where("reset_token", $resetToken)->first();

        if (is_null($user)) {
            return [
                "type" => "warning",
                "msg" => "Istifadeci tapilmadi"
            ];
        }

        if ($user->status_id == UserStatusEnum::BLOCKED->value) {
            return [
                "type" => "danger",
                "msg" => "Istifadeci fealiyyet mehdudlasdirilmisdir"
            ];
        }

        $user->otp = generateOtp();
        $user->save();

//        send otp to user's email

        return [
            "type" => "success"
        ];
    }

    function verify($verifyToken)
    {
        $user = User::where("verify_token", $verifyToken)->first();

        if (is_null($user)) {
            return [
                "type" => "warning",
                "msg" => "Istifadeci tapilmadi"
            ];
        }

        if ($user->status_id != UserStatusEnum::UNVERIFIED->value) {
            return [
                "type" => "warning",
                "msg" => "Bu emeliyyat icra oluna bilmez"
            ];
        }

        $user->otp = generateOtp();
        $user->save();

        return [
            "type" => "success"
        ];
    }

    function logout()
    {
        $user = Auth::user();
        $user->status_id = UserStatusEnum::OFFLINE->value;
        $user->save();

        return [
            "type" => "success"
        ];
    }

}
