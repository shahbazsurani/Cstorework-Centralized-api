<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'SuperAdmin';
    case ApplicationAdmin = 'ApplicationAdmin';
    case LocationAdmin = 'LocationAdmin';
    case UserAdmin = 'UserAdmin';
    case ReadOnly = 'ReadOnly';
    case ReadWrite = 'ReadWrite';
}
