<?php

namespace VocoLabs\RollNumber\Models;

use Illuminate\Database\Eloquent\Model;

class RollType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'grouping_model',
    ];
}
