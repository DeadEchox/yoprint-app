<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'unique_key',
        'title',
        'description',
        'color_name',
        'size',
        'style#',
        'piece_price',
        'sanmar_mainframe_color'
    ];
}
