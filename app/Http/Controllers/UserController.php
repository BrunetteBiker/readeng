<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use App\Models\UserStatus;
use App\Enum\UserRole as UserRoleEnum;
use App\Enum\UserStatus as UserStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    function search(Request $request)
    {
        $like = $request->get("like", []);
        $orderBy = $request->get("order-by", "id|desc");
        $orderBy = Str::of($orderBy)->explode("|")->collect();
        $take = $request->get("take", 25);
        $whereIn = $request->get("where-in", []);
        $skip = $request->get("skip", 0) * $take;


        $users = new User();

        foreach ($like as $column => $value) {
            $users = $users->where($column, "like", "%$value%");
        }

        foreach ($whereIn as $column => $array) {
            $users = $users->whereIn($column, $array);
        }

        $count = $users->count();
        $records = $users->take($take)->skip($skip)->orderBy($orderBy->first(), $orderBy->last())->get();

        return [
            "count" => $count,
            "records" => $records
        ];


    }

    function create(Request $request)
    {
        $fullname = $request->get("fullname");
        $email = $request->get("email");
        $phone = $request->get("phone");
        $roleId = $request->get("role-id");

        $validator = Validator::make([
            "fullname" => $fullname,
            "email" => $email,
            "phone" => $phone,
            "roleId" => $roleId,
        ], [
            "fullname" => "required",
            "email" => "required|email|unique:users",
            "phone" => "required|regex:/^\+[0-9]+$/|unique:users",
            "roleId" => "required",
        ], [
            "fullname.required" => "",
            "email.required" => "",
            "email.email" => "",
            "email.unique" => "",
            "phone.required" => "",
            "phone.unique" => "",
            "phone.regex" => "",
        ]);


        if ($validator->fails()) {
            return [
                "type" => "danger",
                "msg" => $validator->errors()->first()
            ];
        }
        $user = new User();


        $user->fullname = $fullname;
        $user->email = $email;
        $user->phone = $phone;
        $user->role_id = $roleId;
        $user->status_id = UserStatusEnum::UNVERIFIED->value;

        $user->save();
        $user->nickname = "user" . now()->getTimestamp() . Str::of($user->id)->padLeft(6, 0);
        $user->pin = "REU-" . Str::of($user->id)->padLeft(6, 0);
        $user->reset_token = generateToken($user->id);

        $user->save();

        return [
            "type" => "success",
            "msg" => "Yeni istifadeci portala elave olundu"
        ];


    }
    function modify(Request $request, $id)
    {
        $user = User::find($id);
        $freshInputs = $request->collect()->filter();

        $fullname = $freshInputs->get("fullname", $user->fullname);
        $email = $freshInputs->get("email", $user->email);
        $phone = $freshInputs->get("phone", $user->phone);
        $roleId = $freshInputs->get("role-id", $user->role_id);

        $vData = [
            "fullname" => $fullname,
            "role_id" => $roleId,
        ];
        $vRules = [
            "fullname" => "required",
            "role_id" => "required|numeric",
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
        $user->role_id = $roleId;
        $user->save();

        return [
            "type" => "success",
            "msg" => "Duzelisler qeyde alindi"
        ];

    }
    function resetPassword($id)
    {
        $user = User::find($id);
        $user->reset_token = generateToken($id);
        $user->save();

        return [
            "type" => "success",
            "msg" => "Sifre berpasi ucun melumatlar gonderildi"
        ];

    }
    function block($id)
    {
        $user = User::find($id);
        $user->status_id = UserStatusEnum::BLOCKED->value;
        $user->save();

        return [
            "type" => "success",
            "msg" => "Istifadeci fealiyyeti mehdudlasdirildi"
        ];
    }
    function unblock($id)
    {
        $user = User::find($id);
        $user->status_id = UserStatusEnum::OFFLINE->value;
        $user->save();

        return [
            "type" => "success",
            "msg" => "Istifadeci fealiyyeti aktivlesdirildi"
        ];
    }


}
