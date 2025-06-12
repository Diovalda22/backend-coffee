<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetails extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
}
