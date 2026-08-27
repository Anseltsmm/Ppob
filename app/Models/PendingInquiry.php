<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingInquiry extends Model
{
    protected $fillable = [
        'ref_id',
        'product_code',
        'destination',
        'user_id',
        'status',
        'customer_name',
        'raw',
    ];
}
