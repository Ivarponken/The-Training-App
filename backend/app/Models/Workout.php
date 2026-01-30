<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $fillable = [
        'when',
        'activity',
        'details',
        'borg_scale',
        'distance',
        'duration',
        'image_path'
    ];
}
