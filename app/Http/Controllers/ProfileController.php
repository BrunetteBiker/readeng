<?php

namespace App\Http\Controllers;

use App\Enum\UserStatus as UserStatusEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    function modify(Request $request)
    {
        $user = Auth::user();
        $freshInputs = $request->collect()->filter();

        $fullname = $freshInputs->get("fullname", $user->fullname);
        $email = $freshInputs->get("email", $user->email);
        $phone = $freshInputs->get("phone", $user->phone);

        $vData = [
            "fullname" => $fullname,
        ];
        $vRules = [
            "fullname" => "required",
        ];

        $vMsgs = [
            "fullname.required" => "Ad ve soyad daxil edilmelidir",
            "role_id.required" => "Selahiyyet daxil edilmelidir",
            "role_id.numeric" => "Selahiyyet duzgun deyil",
            "status_id.required" => "Status daxil edilmelidir",
            "status_id.numeric" => "Status duzgun deyil",
        ];

        if ($email != $user->email) {
            $vData["email"] = $email;
            $vRules["email"] = "required|email|unique:users";
            $vMsgs["email.required"] = "E-poct unvani daxil edilmelidir";
            $vMsgs["email.email"] = "E-poct unvani duzgun deyil";
            $vMsgs["email.unique"] = "E-poct unvani istifade olunmusdur";
        }
        if ($phone != $user->phone) {
            $vData["phone"] = $phone;
            $vRules["phone"] = "regex:/^\+[0-9]+$/|unique:users";
            $vMsgs["phone.regex"] = "Elaqe nomresi duzgun deyil";
            $vMsgs["phone.unique"] = "Elaqe nomresi istifade olunmusdur";
        }

        $validator = Validator::make($vData, $vRules, $vMsgs);

        if ($validator->fails()) {
            return [
                "type" => "warning",
                "msg" => $validator->errors()->first()
            ];
        }

        $user->fullname = $fullname;
        $user->email = $email;
        $user->phone = $phone;
        $user->save();

        return [
            "type" => "success",
            "msg" => "Duzelisler qeyde alindi"
        ];

    }

    function resetPassword(Request $request, $resetToken = null)
    {

        $otp = $request->get("otp");
        $oldPassword = $request->get("old-password");
        $newPassword = $request->get("new-password");
        $rePassword = $request->get("re-password");


        $vData = [
            "new-password" =>$newPassword,
            "re-password" =>$rePassword,
            "old-password" =>$oldPassword,
        ];
        $vRules = [
            "new-password"=>"required|min:6",
            "re-password"=>"required|same:new-password"
        ];

        $vMsgs = [
            "new-password.required"=>"Yeni sifre daxil edilmelidir",
            "new-password.min"=>"Yeni sifre minimum alti (6) simvoldan ibaret olmalidir",
            "re-password.required"=>"Tekrar sifre daxil edilmelidir",
            "re-password.same"=>"Tekrar sifre ve yeni sifre eyni olmalidir",
        ];


        if (!is_null($resetToken)) {

            $user = User::where("reset_token", $resetToken)->first();

        } else {

            $user = Auth::user();
            $vRules["old-password"] = "required";
            $vMsgs["old-password.required"] = "Kohne sifre daxil edilmelidir";

        }

        $validator = Validator::make($vData,$vRules,$vMsgs);


        if ($validator->fails()) {
            return [
                "type" => "warning",
                "msg" => $validator->errors()->first()
            ];
        }


        if (!is_null($resetToken) && $otp != $user->otp) {
            return [
                "type" => "danger",
                "msg" => "OTP kod duzgun deyil.Yeni OTP kod e-poct unvaniniza gonderildi"
            ];
        }

        if (is_null($resetToken) && !Hash::check($request->get("old-password"), $user->password)) {
            return [
                "type" => "danger",
                "msg" => "Kohne sifre duzgun deyil"
            ];
        }

        $user->password = Hash::make($request->get("new-password"));

        $user->save();

        return [
            "type" => "success",
            "msg" => "Sifre deyisdirildi"
        ];
    }

    function verify(Request $request, $verifyToken)
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

        $otp = $request->get("otp");

        if ($otp != $user->otp) {
            return [
                "type" => "danger",
                "msg" => "OTP kod duzgun deyil.Yeni OTP kod e-poct unvaniniza gonderildi"
            ];
        }

        $user->status_id = UserStatusEnum::ONLINE->value;
        $user->otp = null;
        $user->verify_token = null;
        $user->save();

        return [
            "type" => "success",
            "msg" => "Tesdiqlenme ugurlu oldu"
        ];


    }
}
