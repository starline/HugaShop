<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Models;

use HugaShop\Services\Config;
use Illuminate\Support\Str;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Traits\CheckModelTrait;
use HugaShop\Models\Traits\TranslationTrait;
use Illuminate\Database\Capsule\Manager as DB;
use HugaShop\Services\Cache;
use Symfony\Contracts\Cache\ItemInterface;

abstract class BaseModel extends Model
{

    use TranslationTrait, CheckModelTrait;

    protected static $IsAutoBooted = false;

    protected $guarded = [];
    public $timestamps = false;

    protected static $table_fields;

    public function __construct(array $attributes = [])
    {
        self::autoBootDB();

        // Auto DB table naming
        $this->table ?? $this->table = Str::snake(class_basename(static::class));

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

        $capsule = new DB;
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
        if (Config::get('database')->debug) {
            DB::connection()->listen(function ($query) {
                $dump_result =  "SQL: {$query->sql}\n";
                $dump_result .= "Binding: " . implode(', ', $query->bindings) . "\n";
                $dump_result .= "Time: {$query->time}ms";
                dump($dump_result);
            });
        }

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
     * Get translatable fields
     * @return array Array of field names that have 'trans' => true
     */
    public static function getTranslatableFields(): array
    {
        $fields = [];
        foreach (static::getFields() as $name => $params) {
            if (!empty($params['trans'])) {
                $fields[] = $name;
            }
        }

        return $fields;
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
    public static function createOne(array|object $values): object
    {
        $values = self::validateValues($values);

        $model = static::getModel();
        return $model->runWithInitTable(function () use ($model, $values) {
            return $model->newQuery()->create($values);
        });
    }


    /**
     * Update one by ID
     */
    public static function updateOne(int|array $ids, array|object $values)
    {
        if ($language_code = Language::checkOrGetCode() and static::isTranslatable()) {
            $values = static::separateValues($values, $language_code);
        }

        $values = self::validateValues($values);

        $model = static::getModel();
        return $model->runWithInitTable(function () use ($model, $ids, $values) {
            return $model->newQuery()->whereId($ids)->update($values);
        });
    }


    /**
     * Delete one by ID
     */
    public static function deleteOne(array|int $ids)
    {
        return self::query()->whereId($ids)->delete();
    }


    /**
     * Delete by field
     * @param string $field
     * @param $value
     */
    public static function deleteBy(string $field, $value): int
    {
        return self::query()->where($field, $value)->delete();
    }


    /**
     * Get list
     * @param array $filter
     * @param array|string $order ['id', 'DESC]
     * @param array $join [Order:class, User::class]
     * @param string $select
     * @param int $cache Cache lifetime in seconds
     */
    public static function getList(array $filter = [], array|string $order = [], array|string $join = [], ?string $select = null, ?int $cache = 0)
    {
        $model = static::getModel();
        $query = $model->newQuery();

        // JOINs
        if (!empty($join)) {
            $query->with(is_string($join) ? [$join] : $join);
        }

        // Сортировка
        if (is_string($order)) {
            if ($order == 'position') {
                $order = [$order, 'asc'];
            } else {
                $order = [$order, 'asc'];
            }
        }

        if (!empty($order)) {
            $query->orderBy($order[0], $order[1] ?? 'asc');
        }

        // Pagination
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

        $callback = function () use ($model, $query, $select) {
            return $model->runWithInitTable(function () use ($query, $select) {
                if ($select) {
                    return $query->pluck($select)->toArray();
                }

                $result = $query->get();

                foreach ($result as $item) {
                    $item->settings = empty($item->settings) ? new \stdClass() : (object) unserialize($item->settings);
                }

                return $result;
            });
        };

        // Caching
        if ($cache > 0 || is_null($cache)) {
            $cache_key = 'list_' . md5(json_encode([$filter, $order, $join, $select]));
            return Cache::cache(static::class)->get($cache_key, function (ItemInterface $item) use ($callback, $cache) {
                $item->expiresAfter($cache);
                return $callback();
            });
        }

        return $callback();
    }


    /**
     * Get count
     * @param array $filter
     */
    public static function getCount(array $filter = []): int
    {
        $model = static::getModel();
        $query = $model->newQuery();

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

        return $model->runWithInitTable(function () use ($query) {
            return $query->count();
        });
    }


    /**
     * Get one
     * @param int|array $id id | ['id'' => 1, 'name' => 'name']
     * @param array|string $join ['user', 'user.permissions'] | 'user.permissions'
     */
    public static function getOne(int|array $id, array|string $join = [])
    {
        $model = static::getModel();
        $query = $model->newQuery();

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

        $result = $model->runWithInitTable(function () use ($query) {
            return $query->first();
        });

        // Settings
        if ($result) {
            $result->settings = empty($result->settings) ? new \stdClass() : (object) unserialize($result->settings);
        }

        return $result;
    }


    /**
     * Prepare values
     * @param object|array $entity
     */
    public static function validateValues(object|array $values): array
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
     * Model Cache clean
     */
    public static function cacheClear()
    {
        Cache::cache(static::class)->clear();
    }
}
