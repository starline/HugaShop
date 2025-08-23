<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace HugaShop\Addons\DatabaseCheck\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Routing\Attribute\Route;

final class DatabaseCheckController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/DatabaseCheck', name: 'AddonDatabaseCheck', priority: 20)]
    public function index()
    {
        $models = $this->getModels();
        Design::assign('models', $models);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('index.tpl');
    }


    #[Route('/DatabaseCheck/check', name: 'AddonDatabaseCheckCheck', priority: 20)]
    public function check()
    {
        $this->checkAdminAccess('addon', true);

        $class   = Request::post('model', 'string');
        $status  = 'ok';
        $message = '';
        $rows    = 0;
        $size    = 0;

        try {

            $model = new $class();
            $table = $model->getTable();

            $conn   = DB::connection();
            $schema = DB::schema();

            if (!$schema->hasTable($table)) {
                $status  = 'error';
                $message = 'Таблица не найдена';
            } else {

                $errors = [];

                // check columns exist
                $columns = $schema->getColumnListing($table);
                $fields  = array_keys($class::getFields());
                $missing = array_diff($fields, $columns);
                if (!empty($missing)) {
                    $errors[] = 'Отсутствуют поля: ' . implode(', ', $missing);
                }

                $rows = DB::table($table)->count();

                $db_name = $conn->getDatabaseName();
                $prefix  = $conn->getTablePrefix();

                // Для schema и query builder обычно передаём ИМЯ БЕЗ префикса,
                // а для information_schema нужен РЕАЛЬНЫЙ (с префиксом):
                $real_table = str_starts_with($table, $prefix) ? $table : $prefix . $table;

                $info = DB::selectOne(
                    'SELECT (DATA_LENGTH + INDEX_LENGTH) AS size FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                    [$db_name, $real_table]
                );

                $size = (int)($info->size ?? 0);

                // check indexes
                $keys = $class::getKeys();
                if (!empty($keys)) {
                    $indexes_data = DB::select("SHOW INDEX FROM `{$real_table}`");
                    $indexes = [];
                    foreach ($indexes_data as $idx) {
                        $indexes[$idx->Key_name]['type'] = ($idx->Non_unique == 0) ? 'unique' : 'index';
                        $indexes[$idx->Key_name]['columns'][$idx->Seq_in_index] = $idx->Column_name;
                    }
                    foreach ($indexes as &$idx) {
                        ksort($idx['columns']);
                        $idx['columns'] = array_values($idx['columns']);
                    }
                    unset($idx);

                    $missing_idx = [];
                    foreach ($keys as $key) {
                        $expected_cols = $key['column'];
                        $expected_type = $key['type'] ?? 'index';
                        $found = false;
                        foreach ($indexes as $idx) {
                            if ($idx['type'] === $expected_type && $idx['columns'] === $expected_cols) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $missing_idx[] = implode(', ', $expected_cols);
                        }
                    }
                    if (!empty($missing_idx)) {
                        $errors[] = 'Отсутствуют индексы: ' . implode(', ', $missing_idx);
                    }
                }

                if (!empty($errors)) {
                    $status  = 'error';
                    $message = implode('; ', $errors);
                }
            }
        } catch (\Throwable $e) {
            $status  = 'error';
            $message = $e->getMessage();
        }

        return $this->json([
            'model' => class_basename($class),
            'status' => $status,
            'message' => $message,
            'rows'   => $rows,
            'size'   => Helper::convertBytes($size),
        ]);
    }


    /**
     * Get Models
     */
    private function getModels(): array
    {
        $baseDir = dirname(__DIR__, 3) . '/Models';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseDir));
        $models = [];

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace($baseDir . '/', '', $file->getPathname());
            $class = 'HugaShop\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $relative);

            if (!class_exists($class) || !is_subclass_of($class, \HugaShop\Models\BaseModel::class)) {
                continue;
            }

            if (class_basename($class) === 'AbstractTranslation') {
                continue;
            }

            $instance = new $class();
            $models[] = [
                'class' => $class,
                'name'  => class_basename($class),
                'table' => $instance->getTable(),
            ];
        }

        usort($models, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $models;
    }
}
