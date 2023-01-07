<?php

namespace VocoLabs\RollNumber\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Vocolabs\Contracts\Database\DriverException;
use VocoLabs\RollNumber\Models\RollNumber;
use VocoLabs\RollNumber\Models\RollType;

final class NextRollNumber
{
    /**
     * THIS FUNCTION MUST BE CALLED FROM WITHIN A "DB TRANSACTION"
     */
    final public static function get(string $name, string $prefix = '')
    {
        return self::getNextNumber($name, null, null, $prefix);
    }

    /**
     * THIS FUNCTION MUST BE CALLED FROM WITHIN A "DB TRANSACTION"
     */
    final public static function getModelBased(string $name, string $model, int|string $model_id, string $prefix = '')
    {
        return self::getNextNumber($name, $model, $model_id, $prefix);
    }

    // -------------------------------------------------------------------------
    // Private functions
    // -------------------------------------------------------------------------

    private static function getNextNumber(string $name, ?string $model_class, mixed $model_id, string $prefix)
    {
        // Get PDO instance
        $connection = DB::getPdo();

        if ($connection instanceof \PDO && true !== $connection->inTransaction()) {
            throw new DriverException('Database transaction not yet initiated');
        }

        $type = RollType::where('name', $name)->where('model_class', $model_class)->first();

        if (null === $type) {
            $type = self::createRollTYpe($name, $model_class);

            return self::createRollNumber($type, $model_id, $prefix);
        }

        $number = RollNumber::where('type_id', $type->id)->where('model_id', $model_id)->first();

        if (null === $number) {
            return self::createRollNumber($type, $model_id, $prefix);
        }

        $sql = 'UPDATE `' . DB::getTablePrefix() . 'roll_numbers`'
            . ' SET `next_number` = CASE'
            . ' WHEN (`rollover_limit` IS NULL OR `rollover_limit` > `next_number`)'
            . '   THEN (LAST_INSERT_ID(`next_number`) + 1)'
            . ' ELSE (LAST_INSERT_ID(`next_number`) - `next_number` + 1)'
            . ' END'
            . ' WHERE `type_id` = ? AND `model_id` '
            . ($model_id === null ? ' IS NULL ' : ' = ?');

        $query_params = $model_id === null ? [$type->id] : [$type->id, $model_id];

        DB::statement($sql, $query_params);

        // Get roll number as LAST_INSERT_ID()
        $next_number = DB::getPdo()->lastInsertId();

        if (empty($next_number)) {
            // @TODO - use custom exception class
            throw new RuntimeException('Could not generate roll number.');
        }

        return self::prefixedNumber($next_number, $prefix);
    }

    private static function createRollTYpe(string $name, ?string $model_class): RollType
    {
        $type = new RollType();
        $type->name = $name;
        $type->model_class = $model_class;

        $type->save();

        return $type;
    }

    private static function createRollNumber(RollType $type, mixed $model_id, string $prefix): int
    {
        if ($model_id && empty($type->model_class)) {
            // @TODO - use custom exception class
            throw new RuntimeException('Model class should be specified in order to get model based roll number.');
        }

        $number = new RollNumber();
        $number->type_id = $type->id;
        $number->model_id = $model_id;
        $number->next_number = 2;

        $number->save();

        return self::prefixedNumber(1, $prefix);
    }

    private static function prefixedNumber(int $number, string $prefix)
    {
        return empty($prefix) ?
            $number :
            $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
