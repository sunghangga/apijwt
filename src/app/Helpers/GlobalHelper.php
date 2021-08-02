<?php
namespace App\Helpers;

use App\Models\Purchases;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class GlobalHelper {

    public static function dayToInt($strDay) {
        if (str_contains(config('const.day_in_week')['sunday'], $strDay)) {
            return config('const.day_to_int')['Sunday'];
        }
        else if (str_contains(config('const.day_in_week')['monday'], $strDay)) {
            return config('const.day_to_int')['Monday'];
        }
        else if (str_contains(config('const.day_in_week')['tuesday'], $strDay)) {
            return config('const.day_to_int')['Tuesday'];
        }
        else if (str_contains(config('const.day_in_week')['wednesday'], $strDay)) {
            return config('const.day_to_int')['Wednesday'];
        }
        else if (str_contains(config('const.day_in_week')['thursday'], $strDay)) {
            return config('const.day_to_int')['Thursday'];
        }
        else if (str_contains(config('const.day_in_week')['friday'], $strDay)) {
            return config('const.day_to_int')['Friday'];
        }
        else {
            return config('const.day_to_int')['Saturday'];
        }
    }

    public static function randMail() {
        $obj = new User;
        return $obj->getNextId().'@gmail.com';
    }

    public static function randPass() {
        return Hash::make('Password123');
    }

    public static function getPrNo() {
        $obj = new Purchases;
        return 'PR.'.Carbon::now()->format('ymd').'.'.str_pad($obj->getNextId(), 8, '0', STR_PAD_LEFT);
    }

    public static function userById($id) {
        $obj = User::selectRaw('id, name, email, email_verified_at, role_id, AsText(location) AS location, company_id, created_at, updated_at')
                        ->where('id', '=', $id)
                        ->get();
        return $obj;
    }
}