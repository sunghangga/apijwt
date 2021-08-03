<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "purchase_detail";
    protected $fillable = [
        'purchases_id',
        'product_id',
        'price',
        'qty',
        'created_at',
        'updated_at'
    ];
}
