<?php
namespace App\Enum;

enum UserRole:int{
    case CUSTOMER = 1;
    case MODERATOR = 2;
    case ADMIN = 3;
}
