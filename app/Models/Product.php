<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model; // ใช้งาน NoSQL ตัวนี้ถูกต้องแล้วครับ

class Product extends Model
{
    use HasFactory;

    // ชี้เป้าไปที่การเชื่อมต่อ MongoDB ที่แยกไว้ใน .env และ database.php
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'images',
        'is_active',
        'specifications',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'price' => 'float',
        'stock' => 'integer',
        'specifications' => 'array', 
    ];
}