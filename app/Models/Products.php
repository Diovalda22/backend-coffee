<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];
    protected $with = ['category'];

    public function category()
    {
        return $this->belongsTo(ProductCategories::class, 'product_category_id');
    }
}
