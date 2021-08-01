<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerBalances extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "customer_balances";
    protected $fillable = [
        'ac_code',
        'users_id',
        'debit',
        'credit',
        'description',
        'refno',
    ];
}
