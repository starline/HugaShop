<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace HugaShop\Models;

use Illuminate\Support\Str;
use HugaShop\Services\Cache;
use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use HugaShop\Models\Localization\Language;
use Symfony\Contracts\Cache\ItemInterface;
use HugaShop\Models\Traits\CheckModelTrait;
use HugaShop\Models\Traits\TranslationTrait;
use Illuminate\Database\Capsule\Manager as DB;

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

        // Auto add created_at updated_at fileds
        if ($this->timestamps) {
            static::$table_fields['created_at'] = ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'];
            static::$table_fields['updated_at'] = ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'];
        }

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
     * Get Model Instance
     */
    public static function getModel()
    {
        return new static;
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
     * Get searchable fields
     */
    public static function getSearchFields(): array
    {
        $fields = [];
        foreach (static::getFields() as $name => $params) {
            if (!empty($params['search'])) {
                $fields[] = $name;
            }
        }

        return $fields;
    }


    /**
     * Check if Model has position
     */
    public static function hasPosition(): bool
    {
        return array_key_exists('position', static::getFields());
    }


    /**
     * Check if Model has url
     */
    public static function hasUrl(): bool
    {
        return array_key_exists('url', static::getFields());
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
        $model = static::getModel();

        // If Table has url. Make it uniq
        if ($model::hasUrl()) {
            $values = Helper::makeUniqSlug(static::class, $values);
        }

        $values = self::validateValues($values);

        $entity = $model->runWithInitTable(function () use ($model, $values) {
            return $model->newQuery()->create($values);
        });

        // Make position same as id by default
        if (!isset($values['position']) and $model::hasPosition()) {
            $entity->position = $entity->id;
            $entity->save();
        }

        return $entity;
    }


    /**
     * Update one by ID
     */
    public static function updateOne(int $id, array|object $values)
    {
        $model = static::getModel();

        if ($language_code = Language::checkOrGetCode() and static::isTranslatable()) {
            $values = static::separateTranslationData($values, $language_code);
        }

        // If Table has url. Make it uniq
        if ($model::hasUrl()) {
            $values = Helper::makeUniqSlug(static::class, $values);
        }

        $values = self::validateValues($values);

        return $model->runWithInitTable(function () use ($model, $id, $values) {
            return $model->newQuery()->whereId($id)->update($values);
        });
    }


    /**
     * Update list by IDs
     */
    public static function updateList(array $ids, array|object $values)
    {
        $model = static::getModel();
        $values = self::validateValues($values);
        return $model->runWithInitTable(function () use ($model, $ids, $values) {
            return $model->newQuery()->whereId($ids)->update($values);
        });
    }


    /**
     * Delete one by ID
     */
    public static function deleteOne(array|int $ids)
    {
        if (static::isTranslatable()) {
            static::deleteTranslations($ids);
        }
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
     * @param array|string $order ['id', 'asc]
     * @param array $join ['user', 'user.permissions'] | 'user.permissions'
     * @param string $select
     * @param int $cache Cache lifetime in seconds
     */
    public static function getList(array $filter = [], array|string $order = [], array|string $join = [], ?string $select = null, bool $count = false, ?int $cache = 0)
    {
        $model = static::getModel();
        $query = $model->newQuery();

        if (!$count) {

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
            $page   = $filter['page'] ?? 1;
            $limit  = $filter['limit'] ?? null;

            if ($limit !== null and $limit !== 'all') {
                $page  = max(1, (int) $page);
                $limit = max(1, (int) $limit);
                $query->offset(($page - 1) * $limit)->limit($limit);
            }
        }

        // Поиск по ключевому слову
        if (!empty($filter['search'])) {
            $search_fields = static::getSearchFields();
            $query->where(function (Builder $sub_query) use ($search_fields, $filter) {
                foreach ($search_fields as $field) {
                    $sub_query->orWhere($field, 'like', '%' . $filter['search'] . '%');
                }
            });
        }

        // Reset extra filters
        unset($filter['page'], $filter['limit'], $filter['search']);

        // Filtering
        foreach ($filter as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        // Results
        $callback = function () use ($model, $query, $select, $count) {
            return $model->runWithInitTable(function () use ($query, $select, $count) {

                // Get Count
                if ($count) {
                    return $query->count();
                }

                // Get select field array
                if ($select) {
                    return $query->pluck($select)->toArray();
                }

                // Get List
                $result = $query->get();
                foreach ($result as $item) {
                    if (isset($item->settings)) {
                        $item->settings = empty($item->settings) ? new \stdClass() : (object) unserialize($item->settings);
                    }
                }
                return $result;
            });
        };

        // Caching
        if ($cache > 0 || is_null($cache)) {
            $cache_key = ($count ? 'count_' : 'list_') . md5(json_encode([$filter, $order, $join, $select]));
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
     * @param ?int $cache 
     */
    public static function getCount(array $filter = [], ?int $cache = 0): int
    {
        return static::getList($filter, count: true, cache: $cache);
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
            $result = $query->first();

            // Settings
            if (isset($result->settings)) {
                $result->settings = empty($result->settings) ? new \stdClass() : (object) unserialize($result->settings);
            }

            return $result;
        });

        return $result;
    }


    /**
     * Check if url already exists
     */
    public static function urlExists(string $url, ?string $entity_id = null)
    {
        $model = static::getModel();
        if (!$model::hasUrl()) {
            return false;
        }

        return $model->runWithInitTable(function () use ($model, $url, $entity_id) {
            return $model->newQuery()->where('url', $url)
                ->where('id', '!=', $entity_id) # исключаем текущий id
                ->exists();
        });
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
            foreach (static::$table_fields as $param_name => $param_settings) {
                if (array_key_exists($param_name, $values)) {
                    if (!($param_settings['type'] === 'datetime' and $values[$param_name] === '')) {
                        $valid_values[$param_name] = $values[$param_name];
                    }
                }
            }
        } else {
            $valid_values = $values;
        }

        // Очищаем primery key
        unset($valid_values['id']);

        // Convert Settings. Array to json
        if (isset($valid_values['settings'])) {
            $settings = empty($valid_values['settings']) ? [] : (array) $valid_values['settings'];
            $valid_values['settings'] = serialize($settings);
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
