<?php

use VocoLabs\RollNumber\Support\NextRollNumber;

/**
 * @deprecated
 */
function next_reference_code($name)
{
    return NextRollNumber::get($name);
}

function roll_number(string $name, string $custom_prefix = '')
{
    return NextRollNumber::get($name);
}

function model_roll_number(string $name, string $model_name, int|string $model_id, string $custom_prefix = '')
{
    return NextRollNumber::get($name);
}
