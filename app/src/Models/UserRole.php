<?php

namespace App\Models;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case EMPLOYEE = 'employee';
}
