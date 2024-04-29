<?php
namespace App\Enum;

enum UserStatus:int{
    case OFFLINE = 1;
    case ONLINE = 2;
    case UNVERIFIED = 3;
    case BLOCKED = 4;
}
