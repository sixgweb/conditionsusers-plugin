<?php

namespace Sixgweb\ConditionsUsers\Classes;

use RainLab\User\Models\User;
use RainLab\User\Models\UserGroup;

class UserHelper
{
    public static function getUserIdOptions()
    {
        return User::get()->pluck('email', 'id')->toArray();
    }

    public static function getUserGroupIdOptions()
    {
        return UserGroup::lists('name', 'id');
    }
}
