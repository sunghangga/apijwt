<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessHours extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "business_hours";
    protected $fillable = [
        'day',
        'open_time',
        'end_time',
        'company_id',
    ];
}
