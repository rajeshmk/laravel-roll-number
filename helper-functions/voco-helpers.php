<?php

use VocoLabs\RollNumber\Support\NextRollNumber;

function simple_roll_number(string $name, string $prefix = '')
{
    return NextRollNumber::get($name, $prefix);
}

function model_based_roll_number(string $name, string $model, int|string $model_id, string $prefix = '')
{
    return NextRollNumber::getModelBased($name, $model, $model_id, $prefix);
}
