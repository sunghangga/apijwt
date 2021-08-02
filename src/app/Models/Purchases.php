<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Purchases extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "purchases";
    protected $fillable = [
        'users_id',
        'company_id',
        'pr_no',
        'total',
        'pay_status',
        'qty_total',
    ];

    public function getNextId() 
    {
        $statement = DB::select("show table status like 'purchases'");
        return $statement[0]->Auto_increment;
    }
}
