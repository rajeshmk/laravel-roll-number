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
    final public static function get(string $name, string $custom_prefix = '')
    {
        return self::getNextNumber($name, null, null, $custom_prefix);
    }

    /**
     * THIS FUNCTION MUST BE CALLED FROM WITHIN A "DB TRANSACTION"
     */
    final public static function getForModel(string $name, string $model_name, int|string $model_id, string $custom_prefix = '')
    {
        return self::getNextNumber($name, $model_name, $model_id, $custom_prefix);
    }

    // -------------------------------------------------------------------------
    // Private functions
    // -------------------------------------------------------------------------

    private static function getNextNumber(string $name, ?string $model_name, mixed $model_id, string $custom_prefix)
    {
        // Get PDO instance
        $connection = DB::getPdo();

        if ($connection instanceof \PDO && true !== $connection->inTransaction()) {
            throw new DriverException('Database transaction not yet initiated');
        }

        $type = RollType::where('name', $name)->where('model_name', $model_name)->first();

        if (null === $type) {
            $type = self::createRollTYpe($name, $model_name);

            return self::createRollNumber($type, $model_id, $custom_prefix);
        }

        $number = RollNumber::where('type_id', $type->id)->where('model_id', $model_id)->first();

        if (null === $number) {
            return self::createRollNumber($type, $model_id, $custom_prefix);
        }

        $sql = 'UPDATE `' . DB::getTablePrefix() . 'roll_numbers`'
            . ' SET `next_number` = CASE'
            . ' WHEN (`rollover_limit` IS NULL OR `rollover_limit` > `next_number`)'
            . '   THEN (LAST_INSERT_ID(`next_number`) + 1)'
            . ' ELSE (LAST_INSERT_ID(`next_number`) - `next_number` + 1)'
            . ' END'
            . ' WHERE `type_id` = ? AND `model_id` = ?';

        DB::statement($sql, [$type->id, $model_id]);

        // Get roll number as LAST_INSERT_ID()
        $next_number = DB::getPdo()->lastInsertId();

        if (empty($next_number)) {
            // @TODO - use custom exception class
            throw new RuntimeException('Could not generate roll number');
        }

        return self::prefixedNumber($next_number, $custom_prefix);
    }

    private static function createRollTYpe(string $name, string $model_name): RollType
    {
        $type = new RollType();
        $type->name = $name;
        $type->model_name = $model_name;

        $type->save();

        return $type;
    }

    private static function createRollNumber(RollType $type, mixed $model_id, string $custom_prefix): int
    {
        $number = new RollNumber();
        $number->type_id = $type;
        $number->model_id = $model_id;
        $number->next_number = 1;

        $number->save();

        return self::prefixedNumber(1, $custom_prefix);
    }

    private static function prefixedNumber(int $number, string $custom_prefix)
    {
        return empty($custom_prefix) ?
            $number :
            $custom_prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
