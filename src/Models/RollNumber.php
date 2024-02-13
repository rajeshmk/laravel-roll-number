<?php

namespace VocoLabs\RollNumber\Models;

use Illuminate\Database\Eloquent\Model;

class RollNumber extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type_id',
        'grouping_id',
        'next_number',
    ];
}
