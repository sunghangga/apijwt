<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
