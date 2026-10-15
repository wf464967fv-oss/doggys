<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Pro = 'pro';
    case Admin = 'admin';
}
