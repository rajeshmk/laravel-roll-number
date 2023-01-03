<?php

use VocoLabs\RollNumber\Support\NextRollNumber;

function next_reference_code($roll_type)
{
    return NextRollNumber::get($roll_type);
}

function next_roll_number($roll_type)
{
    return NextRollNumber::get($roll_type);
}
