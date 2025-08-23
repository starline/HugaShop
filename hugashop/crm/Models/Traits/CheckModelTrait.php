<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Models\Traits;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

trait CheckModelTrait
{

    protected static array $checkedTables = [];


    /**
     * Execute query and try to create table/columns on missing table/column errors
     */
    public function runWithInitTable(callable $callback)
    {
        try {
            return $callback();
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            if (
                stripos($msg, 'no such table') !== false ||
                stripos($msg, 'base table or view not found') !== false ||
                stripos($msg, 'doesn\'t exist') !== false ||
                stripos($msg, 'unknown column') !== false
            ) {
                $table = $this->getTable();
                self::initTable($table);
                return $callback();
            }

            throw $e;
        }
    }


    /**
     * Ensure that the database table and its fields exist
     */
    protected static function initTable(string $table)
    {
        if (isset(self::$checkedTables[$table]) || empty(static::getFields())) {
            self::$checkedTables[$table] = true;
            return;
        }

        $schema = DB::schema();

        if (!$schema->hasTable($table)) {
            $schema->create($table, function (Blueprint $blueprint) {
                static::addColumns($blueprint, static::getFields());
            });
        } else {
            foreach (static::getFields() as $field => $options) {
                if (!$schema->hasColumn($table, $field)) {
                    $schema->table($table, function (Blueprint $blueprint) use ($field, $options) {
                        static::addColumns($blueprint, [$field => $options]);
                    });
                }
            }
        }

        if (!empty(static::getKeys())) {
            static::ensureIndexes($table, static::getKeys());
        }

        self::$checkedTables[$table] = true;
    }


    /**
     * Add columns to Blueprint instance
     */
    protected static function addColumns(Blueprint $blueprint, array $fields): void
    {

        foreach ($fields as $name => $params) {
            $type    = $params['type'] ?? 'varchar';
            $length  = $params['length'] ?? null;
            $default = $params['def'] ?? $params['default'] ?? null;
            $extra   = $params['extra'] ?? null;

            switch ($type) {
                case 'int':
                    $column = ($extra === 'AUTO_INCREMENT')
                        ? $blueprint->increments($name)
                        : $blueprint->integer($name);
                    break;
                case 'tinyint':
                    $column = $blueprint->tinyInteger($name);
                    break;
                case 'char':
                    $column = $blueprint->char($name, $length ?? 1);
                    break;
                case 'varchar':
                    $column = $blueprint->string($name, $length ?? 255);
                    break;
                case 'text':
                    $column = $blueprint->text($name);
                    break;
                case 'mediumtext':
                    $column = $blueprint->mediumText($name);
                    break;
                case 'decimal':
                    if ($length) {
                        [$precision, $scale] = array_pad(explode('.', (string) $length), 2, 0);
                        $column = $blueprint->decimal($name, (int) $precision, (int) $scale);
                    } else {
                        $column = $blueprint->decimal($name, 8, 2);
                    }
                    break;
                case 'datetime':
                    $column = $blueprint->dateTime($name);
                    break;
                case 'date':
                    $column = $blueprint->date($name);
                    break;
                default:
                    $column = $blueprint->string($name, $length ?? 255);
            }

            // Set default only if:
            // - it is defined
            // - the column is not AUTO_INCREMENT
            // - the type supports default values (text/mediumtext do not support it)
            $type_supports = !in_array($type, ['text', 'mediumtext']);
            $is_auto_increment = ($type === 'int' && $extra === 'AUTO_INCREMENT');
            if ($type_supports && !$is_auto_increment) {
                if (is_null($default)) {
                    $column->nullable();
                } else {
                    if ($default === 'CURRENT_TIMESTAMP') {
                        $column->useCurrent();
                    } else {
                        $column->default($default);
                    }
                }
            }
        }
    }


    /**
     * Ensure indexes from $table_keys exist on the table
     */
    protected static function ensureIndexes(string $table, array $indexes): void
    {
        $schema   = DB::schema();
        $existing = self::getTableIndexes($table);

        $missing = [];
        foreach ($indexes as $name => $params) {
            if (!in_array(strtolower($name), $existing, true)) {
                $missing[$name] = $params;
            }
        }

        if ($missing) {
            $schema->table($table, function (Blueprint $blueprint) use ($missing) {
                foreach ($missing as $name => $params) {
                    $columns = $params['column'] ?? [];
                    $type    = $params['type'] ?? 'index';

                    switch ($type) {
                        case 'unique':
                            $blueprint->unique($columns, $name);
                            break;
                        case 'primary':
                            $blueprint->primary($columns, $name);
                            break;
                        default:
                            $blueprint->index($columns, $name);
                    }
                }
            });
        }
    }


    /**
     * Get existing index names for table
     */
    protected static function getTableIndexes(string $table): array
    {
        $connection = DB::connection();
        $indexes = [];

        $prefix     = $connection->getTablePrefix();
        $real_table = str_starts_with($table, $prefix) ? $table : $prefix . $table;

        $rows = $connection->select("SHOW INDEX FROM `{$real_table}`");
        foreach ($rows as $row) {
            $name = is_object($row) ? ($row->Key_name ?? null) : ($row['Key_name'] ?? null);
            if ($name) {
                $indexes[] = strtolower($name);
            }
        }

        return array_unique($indexes);
    }
}
