<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carts extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
