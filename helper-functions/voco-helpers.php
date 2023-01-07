<?php

use VocoLabs\RollNumber\Support\NextRollNumber;

/**
 * @deprecated
 */
function next_reference_code($roll_type)
{
    return NextRollNumber::get($roll_type);
}

function next_roll_number(string $roll_type, string $custom_prefix = '')
{
    return NextRollNumber::get($roll_type);
}

function model_roll_number(string $name, string $model_name, int|string $model_id, string $custom_prefix = '')
{
    return NextRollNumber::get($name);
}
