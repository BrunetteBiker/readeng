<?php

use Illuminate\Support\Facades\Hash;

function generateToken($userId = null)
{
    $token = now()->getTimestamp() . $userId . fake()->randomNumber(6);
    $token = Hash::make($token);
    return $token;
}

function generateOtp()
{
    return fake()->randomNumber(6);
}
