<?php

namespace VocoLabs\RollNumber\Traits;

use Illuminate\Support\Str;

trait HasRollNumber
{
    public static function bootHasRollNumber()
    {
        static::creating(function (self $entity) {
            self::appendRollNumber($entity);
        });
    }

    protected function rollNumberConfig(): string|array
    {
        return [
            'column' => 'roll_number',
            'prefix' => '',
        ];
    }

    private static function appendRollNumber(self $entity)
    {
        $config = $entity->rollNumberConfig();

        $column = is_string($config) ? $config : $config['column'];

        $class_name_snake = str_replace('\\', '', Str::snake(get_class($entity)));

        $roll_number = roll_number($class_name_snake.':'.Str::snake($column))
            ->prefix($config['prefix'] ?? '');

        if (method_exists($entity, 'getRollGroupModelName')) {
            $roll_number->groupBy(
                $entity->getRollGroupModelName(),
                $entity->getRollGroupModelId(),
            );
        }

        // Assign roll number to the required column
        $entity->$column = $roll_number->get();
    }
}
