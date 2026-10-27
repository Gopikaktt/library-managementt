<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category_id',
        'category',
        'quantity',
        'available_quantity',
        'price',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getCategoryAttribute($value)
    {
        if ($this->relationLoaded('category')) {
            return $this->getRelation('category');
        }

        if (!empty($this->attributes['category_id'])) {
            return $this->category()->first();
        }

        return $value;
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }
    

}
