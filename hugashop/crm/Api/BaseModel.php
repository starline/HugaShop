<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Api;

use HugaShop\Api\Config;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

abstract class BaseModel extends Model
{
    protected static $IsAutoBooted = false;

    protected $guarded = [];
    public $timestamps = false;

    public static $table_fields;

    public function __construct(array $attributes = [])
    {
        self::autoBootDB();

        // Auto DB table naming
        $this->table ?? $this->table = Helper::camelToSnakeCase(Helper::class_basename(static::class));

        parent::__construct($attributes);
    }


    /**
     * DB Auto booting
     */
    protected static function autoBootDB()
    {
        if (self::$IsAutoBooted) {
            return;
        }

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => Config::get('database')->driver,
            'host'      => Config::get('database')->server,
            'database'  => Config::get('database')->name,
            'username'  => Config::get('database')->user,
            'password'  => Config::get('database')->password,
            'prefix'    => Config::get('database')->prefix,
            'charset'   => Config::get('database')->charset,
            'collation' => Config::get('database')->collation
        ]);
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // DB query Debuging
        Capsule::connection()->listen(function ($query) {
            $dump_result =  "SQL: {$query->sql}\n";
            $dump_result .= "Binding: " . implode(', ', $query->bindings) . "\n";
            $dump_result .= "Time: {$query->time}ms";
            dump($dump_result);
        });

        self::$IsAutoBooted = true;
    }


    /**
     * Get table fields
     */
    public static function getFields()
    {
        return static::$table_fields ?? [];
    }


    /**
     * WhereId
     * @param int|array $ids
     */
    public static function whereId(int|array $ids)
    {
        if (is_array($ids)) {
            return static::query()->whereIn('id', $ids);
        }
        return static::query()->where('id', $ids);
    }


    /**
     * Create. Return Object with all added params
     * @return object
     */
    public static function create(array|object $values): object
    {
        $values = static::validateValues($values);
        return parent::query()->create($values);
    }


    /**
     * Prepare values
     * @param object|array $entity
     */
    public static function validateValues(object|array $values)
    {

        $values = is_object($values) ? (array) $values : $values;
        $valid_values = [];

        // check allowed params
        if (!empty(static::$table_fields)) {
            foreach (static::$table_fields as $param_name => $v) {
                if (array_key_exists($param_name, $values)) {
                    $valid_values[$param_name] = $values[$param_name];
                }
            }
        } else {
            $valid_values = $values;
        }

        // Очищаем primery key
        unset($valid_values['id']);

        // Convert Settings. Array to json
        if (isset($valid_values['settings'])) {
            $valid_values['settings'] = empty($valid_values['settings']) ? [] : (array) $valid_values['settings'];
            $valid_values['settings'] = serialize($valid_values['settings']);
        }

        return $valid_values;
    }


    /**
     * Delete by field
     * @param string $field
     * @param $value
     */
    public static function deleteBy(string $field, $value): int
    {
        return static::query()->where($field, $value)->delete();
    }


    /**
     * Update one by ID
     */
    public static function updateOne(int|array $ids, array|object $values)
    {
        $values = self::validateValues($values);
        return static::query()->whereId($ids)->update($values);
    }


    /**
     * Delete one by ID
     */
    public static function deleteOne(array|int $ids)
    {
        return self::whereId($ids)->delete();
    }


    /**
     * Get list
     * @param array $filter
     * @param array|string $order ['id', 'DESC]
     * @param array $join [Order:class, User::class]
     * @param string $select
     */
    public static function getList(array $filter = [], array|string $order = [], array|string $join = [], ?string $select = null)
    {
        $query = static::query();



        // JOINs
        if (!empty($join)) {
            $query->with(is_string($join) ? [$join] : $join);
        }

        // Сортировка
        if (is_string($order)) {
            $order = [$order, 'asc'];
        }
        if (!empty($order)) {
            $query->orderBy($order[0], $order[1] ?? 'asc');
        }

        // Пагинация
        $page = $filter['page'] ?? 1;
        $limit = $filter['limit'] ?? null;

        unset($filter['page'], $filter['limit']);

        if ($limit !== null) {
            $offset = ($page - 1) * $limit;
            $query->offset($offset)->limit($limit);
        }

        // Фильтрация
        foreach ($filter as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        // Выбор полей
        if ($select) {
            return $query->pluck($select)->toArray();
        }

        return $query->get();
    }


    /**
     * Get one
     * @param int|array $id id | ['id'' => 1, 'name' => 'name']
     * @param array|string $join 'group' | ['group', 'permissions']
     */
    public static function getOne(int|array $id, array|string $join = [])
    {

        $query = static::query();

        // Eager load relationships
        if (!empty($join)) {
            $query->with(is_string($join) ? [$join] : $join);
        }

        // Apply conditions
        if (is_array($id)) {
            foreach ($id as $field => $value) {
                $query->where($field, $value);
            }
        } else {
            $query->where('id', $id);
        }

        return $query->first();
    }


    /**
     * Get count
     * @param array $filter
     */
    public static function getCount(array $filter = []): int
    {
        $query = static::query();

        // Удаляем параметры пагинации
        unset($filter['page'], $filter['limit']);

        // Применяем where-фильтры
        foreach ($filter as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->count();
    }
}
