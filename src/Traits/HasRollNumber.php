<?php

namespace VocoLabs\RollNumber\Traits;

use VocoLabs\RollNumber\Support\NextRollNumber;

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

        $class_name_snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', get_class($entity)));

        $roll_number = NextRollNumber::create($class_name_snake . ':' . $column)
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
