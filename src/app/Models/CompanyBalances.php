<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBalances extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "company_balances";
    protected $fillable = [
        'ac_code',
        'company_id',
        'debit',
        'credit',
        'description',
        'refno',
    ];
}
