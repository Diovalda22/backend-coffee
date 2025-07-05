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

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function getDiscountedPrice()
    {
        $now = now()->toDateString();
        if ($this->discount_type && $this->discount_start && $this->discount_end && $now >= $this->discount_start && $now <= $this->discount_end) {
            if ($this->discount_type == 1) { // fixed
                return max(0, $this->price - $this->discount_amount);
            } elseif ($this->discount_type == 2) { // percent
                return max(0, $this->price - ($this->price * $this->discount_amount / 100));
            }
        }
        return $this->price;
    }
}
