<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payments extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentsFactory> */
    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];
}
