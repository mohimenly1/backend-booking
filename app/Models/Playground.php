<?php

// app/Models/Playground.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playground extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 
        'name', 
        'description', 
        'price_per_half_hour', 
        'price_per_hour', 
        'images',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'images' => 'array',
        'price_per_half_hour' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
