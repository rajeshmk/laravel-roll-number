<?php

use VocoLabs\RollNumber\Support\NextRollNumber;

function roll_number(string $name)
{
    return NextRollNumber::create($name);
}
