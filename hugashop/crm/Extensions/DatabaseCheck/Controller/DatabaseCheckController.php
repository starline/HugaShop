<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Extensions\DatabaseCheck\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Routing\Attribute\Route;

final class DatabaseCheckController extends BaseAdminController
{
    use BaseExtensionTrait;

    #[Route('/DatabaseCheck', name: 'ExtDatabaseCheck', priority: 20)]
    public function index()
    {
        $models = $this->getModels();
        Design::assign('models', $models);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('index.tpl');
    }


    #[Route('/DatabaseCheck/check', name: 'ExtDatabaseCheckCheck', priority: 20)]
    public function check()
    {
        $this->checkAdminAccess('extension', true);

        $class = Request::post('model', 'string');
        $status = 'ok';
        $rows = 0;
        $size = 0;

        try {

            /** @var \HugaShop\Models\BaseModel $model */
            $model = new $class();
            $table = $model->getTable();

            $conn   = DB::connection();
            $schema = DB::schema();

            if (!$schema->hasTable($table)) {
                $status = 'error';
            } else {

                // check columns exist
                $columns = $schema->getColumnListing($table);
                $fields = array_keys($class::getFields());
                if (!empty(array_diff($fields, $columns))) {
                    $status = 'error';
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
            }
        } catch (\Throwable $e) {
            $status = 'error';
        }

        return $this->json([
            'model' => class_basename($class),
            'status' => $status,
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
