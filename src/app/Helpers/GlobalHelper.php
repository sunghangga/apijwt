<?php
namespace App\Helpers;

class GlobalHelper {

    public static function dayToInt($strDay) {
        if (str_contains(config('const.day_in_week')['sunday'], $strDay)) {
            return config('const.day_to_int')['sunday'];
        }
        else if (str_contains(config('const.day_in_week')['monday'], $strDay)) {
            return config('const.day_to_int')['monday'];
        }
        else if (str_contains(config('const.day_in_week')['tuesday'], $strDay)) {
            return config('const.day_to_int')['tuesday'];
        }
        else if (str_contains(config('const.day_in_week')['wednesday'], $strDay)) {
            return config('const.day_to_int')['wednesday'];
        }
        else if (str_contains(config('const.day_in_week')['thursday'], $strDay)) {
            return config('const.day_to_int')['thursday'];
        }
        else if (str_contains(config('const.day_in_week')['friday'], $strDay)) {
            return config('const.day_to_int')['friday'];
        }
        else {
            return config('const.day_to_int')['saturday'];
        }
    }

    public static function randMail() {
        $faker = Faker\Factory::create();
        return $faker->uniqueEmail;
    }

    public static function randPass() {
        return 'Password123';
    }
}