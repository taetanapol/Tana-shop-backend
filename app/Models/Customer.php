<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'customers';

    protected $guarded = [];

    protected $casts = [
        'points_available' => 'integer',
        'points_used' => 'integer',
        'points_total_earned' => 'integer',
        'point_history' => 'array',
    ];
}
