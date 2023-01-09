<?php

use VocoLabs\RollNumber\Support\NextRollNumber;

function simple_roll_number(string $name, string $prefix = '')
{
    return NextRollNumber::create($name)
        ->prefix($prefix)
        ->get();
}

function model_based_roll_number(string $name, string $model, int|string $parent_id, string $prefix = '')
{
    return NextRollNumber::create($name)
        ->groupBy($model, $parent_id)
        ->prefix($prefix)
        ->get();
}
