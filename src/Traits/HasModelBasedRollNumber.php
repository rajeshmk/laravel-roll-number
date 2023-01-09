<?php

namespace VocoLabs\RollNumber\Traits;

use VocoLabs\RollNumber\Traits\HasRollNumber;

trait HasModelBasedRollNumber
{
    use HasRollNumber;

    abstract protected function getRollGroupModelName(): string;
    abstract protected function getRollGroupModelId(): int|string;
}
