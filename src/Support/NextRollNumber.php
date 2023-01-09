<?php

namespace VocoLabs\RollNumber\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Vocolabs\Contracts\Database\DriverException;
use VocoLabs\RollNumber\Models\RollNumber;
use VocoLabs\RollNumber\Models\RollType;

// @TODO - use custom exception class instead of `RuntimeException`
final class NextRollNumber
{
    private string $name;
    private string $prefix = '';
    private int $zero_padding = 0;
    private string $model_name;
    private int|string $model_id;
    private int $rollover_limit;

    /**
     * PRIVATE constructor
     */
    private function __construct(string $name)
    {
        if (trim($name) === '') {
            throw new RuntimeException('Name required for the roll number type.');
        }

        $this->name = trim($name);
    }

    /**
     * THIS FUNCTION MUST BE CALLED FROM WITHIN A "DB TRANSACTION"
     */
    final public static function create(string $name): static
    {
        return static::newInstance(trim($name));
    }

    public function prefix(string $prefix, int $zero_padding = 0): self
    {
        $this->prefix = $prefix;
        $this->zero_padding = $zero_padding;

        return $this;
    }

    public function groupBy(string $model, int|string $id): self
    {
        if (!class_exists($model)) {
            throw new RuntimeException('Class `' . $model . '` not found.');
        }

        $this->model_name = $model;
        $this->model_id = $id;

        return $this;
    }

    public function rolloverLimit(int $limit): self
    {
        if ($limit > 0) {
            $this->rollover_limit = $limit;
        }

        return $this;
    }

    public function get(): string
    {
        return $this->withPrefix($this->getNextNumber());
    }

    // -------------------------------------------------------------------------
    // Private functions
    // -------------------------------------------------------------------------

    private static function newInstance(string $name): static
    {
        return new static($name);
    }

    private function getNextNumber(): int
    {
        // Get PDO instance
        $connection = DB::getPdo();

        if ($connection instanceof \PDO && true !== $connection->inTransaction()) {
            throw new DriverException('Database transaction not yet initiated');
        }

        $type = RollType::where('name', $this->name)->where('parent_model', $this->model_name)->first();

        if (null === $type) {
            $type = $this->createRollTYpe();

            return $this->createRollNumber($type);
        }

        $number = RollNumber::where('type_id', $type->id)->where('parent_id', $this->model_id)->first();

        if (null === $number) {
            return $this->createRollNumber($type);
        }

        $max_limit = $this->rollover_limit ?? null;

        $sql = 'UPDATE `' . DB::getTablePrefix() . 'roll_numbers`'
            . ' SET `next_number` = CASE'
            . ' WHEN (? IS NULL OR ? > `next_number`)'
            . '   THEN (LAST_INSERT_ID(`next_number`) + 1)'
            . ' ELSE (LAST_INSERT_ID(`next_number`) - `next_number` + 1)'
            . ' END'
            . ' WHERE `type_id` = ? AND `parent_id` '
            . ($this->model_id === null ? ' IS NULL ' : ' = ?');

        $query_params = [
            $max_limit,
            $max_limit,
            $type->id,
        ];

        if ($this->model_id !== null) {
            $query_params[] = $this->model_id;
        }

        DB::statement($sql, $query_params);

        // Get roll number as LAST_INSERT_ID()
        $next_number = DB::getPdo()->lastInsertId();

        if (empty($next_number)) {
            throw new RuntimeException('Could not generate roll number.');
        }

        return $next_number;
    }

    private function createRollTYpe(): RollType
    {
        $type = new RollType();
        $type->name = $this->name;
        $type->parent_model = $this->model_name ?? null;

        $type->save();

        return $type;
    }

    private function createRollNumber(RollType $type): int
    {
        if ($this->model_id && !$type->parent_model) {
            throw new RuntimeException('Model class should be specified in order to get model based roll number.');
        }

        $number = new RollNumber();
        $number->type_id = $type->id;
        $number->parent_id = $this->model_id;
        $number->next_number = 2;

        $number->save();

        return 1;
    }

    private function withPrefix(int $number): string
    {
        return $this->prefix . str_pad($number, $this->zero_padding, '0', STR_PAD_LEFT);
    }
}
