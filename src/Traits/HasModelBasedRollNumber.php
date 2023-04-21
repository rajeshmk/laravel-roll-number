<?php

namespace VocoLabs\RollNumber\Traits;

trait HasModelBasedRollNumber
{
    use HasRollNumber;

    abstract protected function getRollGroupModelName(): string;

    abstract protected function getRollGroupModelId(): int|string;
}
