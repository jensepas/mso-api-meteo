<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class Meteo extends Model
{
    use HasSpatial;

    protected $fillable = ['title', 'description', 'coordinates'];

    protected $casts = [
        'coordinates' => Point::class,
    ];
}
