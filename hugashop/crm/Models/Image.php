<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 4.4
 * 
 * Intervention Image
 * @link https://image.intervention.io/v3/getting-started/installation
 *
 */

namespace HugaShop\Models;

use HugaShop\Services\Config;
use HugaShop\Services\Helper;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class Image extends BaseModel
{

    private static $allowed_extentions = ['png', 'gif', 'jpg', 'jpeg', 'ico', 'webp', 'bmp'];
    public static $token_length = 10;

    protected $table = 'content_image';

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar'],
        'entity_id' =>          ['type' => 'int'],
        'entity_name' =>        ['type' => 'varchar'],
        'filename' =>           ['type' => 'varchar'],
        'visible' =>            ['type' => 'tinyint',       'def' => 1],
        'position' =>           ['type' => 'created',       'def' => 0],
        'created_at' =>         ['type' => 'varchar',       'def' => 'CURRENT_TIMESTAMP']
    ];

    protected static $table_indexes = [
        'entity_id' => ['column' => ['entity_id', 'entity_name', 'position'],   'type' => 'index'],
        'filename'  => ['column' => ['filename'], 'type' => 'unique']
    ];

    public function entity()
    {
        return $this->morphTo();
    }


    /**
     * Get images
     * @param int|array $entity_id
     * @param string $entity_name - product
     */
    public static function getImages(int|array $entity_id, string $entity_name, bool $public = false)
    {
        $filter = ['entity_id' => $entity_id, 'entity_name' => $entity_name];
        if ($public) {
            $filter['visible'] = 1;
        }
        return self::getList(filter: $filter, order: 'position');
    }


    /**
     * Add image to DB. Check name
     * @param int $entity_id
     * @param string $entity_name
     * @param string $filename
     * @return int image_id
     */
    public static function addImage(int $entity_id, string $entity_name, string $filename)
    {
        // Check and Make uniq name
        $unique_name = $base_name = $filename;

        $i = 1;
        while (self::where('filename', $unique_name)->exists()) {
            $unique_name = $base_name . '-' . $i++;
        }

        // Создаем изображение
        $image = self::createOne([
            'entity_id'   => $entity_id,
            'entity_name' => $entity_name,
            'filename'    => $unique_name,
        ]);

        return $image->id;
    }


    /**
     * Copy original image file and return new unique filename
     * @param string $filename
     * @return string|null
     */
    public static function copyImage(string $filename): ?string
    {
        $src = Config::get('images_originals_dir') . $filename;
        if (!is_file($src)) {
            return null;
        }

        $ext  = pathinfo($filename, PATHINFO_EXTENSION);
        $base = pathinfo($filename, PATHINFO_FILENAME);

        $unique = $base;
        $i = 1;
        while (self::where('filename', $unique . '.' . $ext)->exists()) {
            $unique = $base . '-' . $i++;
        }

        $new_filename = $unique . '.' . $ext;
        $dest = Config::get('images_originals_dir') . $new_filename;
        if (!@copy($src, $dest)) {
            return null;
        }

        return $new_filename;
    }


    /**
     * Delete Image
     * @param int $id
     */
    public static function deleteImage(int $id)
    {
        $image = self::find($id);
        if (!$image) {
            return;
        }
        // Select file name
        $filename = $image->filename;

        // Delete image by ID
        $image->delete();

        // Select images count by name
        $count = self::where('filename', $filename)->count();

        // If there is NOT image, delete file by name
        if ($count === 0) {
            $file   = pathinfo($filename, PATHINFO_FILENAME);
            $ext    = pathinfo($filename, PATHINFO_EXTENSION);

            // Удалить все ресайзы
            $rezised_images = glob(Config::get('images_resized_dir') . $file . ".*x*." . $ext);
            if (!empty($rezised_images)) {
                foreach ($rezised_images as $f) {
                    @unlink($f);
                }
            }

            @unlink(Config::get('images_resized_dir') . $filename);
        }
    }


    /**
     * Delete images by entity
     * @param int|array $entity_id
     * @param string $entity_type
     */
    public static function deleteEntityImages(int|array $entity_id, string $entity_type): void
    {
        $images = self::getImages($entity_id, $entity_type);
        foreach ($images as $image) {
            self::deleteImage($image->id);
        }
    }


    /**
     * Making Image URL
     * @param $filename
     * @param int $width
     * @param string $flags w - watermark, c - cut for size
     */
    public static function getImageURL(string $filename, int $width = 0, int $height = 0, string $flags = '', ?string $format = 'webp'): string
    {
        $watermark  = str_contains($flags, 'w');
        $cut        = str_contains($flags, 'c');

        $resized_filename = self::addResizeParams($filename, $width, $height, $watermark, $cut, $format);
        return Config::get('root_url') . '/' . Config::get('images_resized_url') . $resized_filename . '?' . Helper::makeToken($resized_filename, self::$token_length);
    }


    /**
     * Upload and add image to Database
     *
     * @param string $temp_filename
     * @param $name
     * @param $entity_id
     * @param $entity_name
     * @param $width
     * @param $height
     *
     * @return $id - image ID
     */
    public static function uploadAddImage(string $temp_filename, string $original_filename, int $entity_id, string $entity_name, ?int $width = null, ?int $height = null)
    {
        $image_name = self::uploadImage($temp_filename, $original_filename, $width, $height);
        if (!empty($image_name)) {
            $image_id = self::addImage($entity_id, $entity_name, (string) $image_name);
            if ($image_id) {
                return $image_id;
            }
        }
        return false;
    }


    /**
     * Создание превью изображения
     *
     * @param $filename файл с изображением (без пути к файлу)
     * @return string имя файла превью
     */
    public static function resize(string $filename)
    {
        $params = self::getResizeParams($filename);
        if (!$params) {
            return false;
        }

        list($root_name, $ext, $width, $height, $set_watermark, $cut) = $params;

        // Найдем оригинальное изображение в DB
        $image = self::where('filename', 'like', "%$root_name%")->first();
        if (empty($image->filename)) {
            return false;
        }

        // Пути к папкам с картинками
        $original_file_path     = Config::get('images_originals_dir') . $image->filename;
        $resized_dir            = Config::get('images_resized_dir');

        // Проверяем что оригинальный файл существует
        if (!is_file($original_file_path)) {
            return false;
        }

        // Make resize dir
        if (!is_dir($resized_dir)) {
            mkdir($resized_dir, 0777, true);
        }

        $resized_file = self::addResizeParams($root_name . '.' . $ext, $width, $height, $set_watermark, $cut);

        // Get watermark image
        $watermark = null;
        if ($set_watermark && is_file(Config::get('images_watermark_file'))) {
            $watermark = Config::get('images_watermark_file');
        }

        $new_file_path  = $resized_dir . $resized_file;
        $sharpen        = Settings::getParam('images_sharpen') ?? 0;
        $format         = $ext;

        return self::resizeUploadImage($original_file_path, $new_file_path, $width, $height, $watermark, $cut, $sharpen, $format);
    }


    /**
     * Resize uploaded images
     */
    public static function resizeUploadImage(string $original_file_path, string $new_file_path, ?int $width = null, ?int $height = null, ?string $watermark = null, ?string $cut = null, int $sharpen = 0, ?string $format = 'webp')
    {

        $quality = Config::get('images_quality');

        // Четкость изображения. 0-100 (больше - четче)
        $sharpen = max(0, min(100, $sharpen));

        // Прозрачность водяного знака. Настройки сайта.
        $watermark_transparency = min(100, (int)Settings::getParam('watermark_transparency'));

        // Сreate image manager with desired driver
        $manager = new ImageManager(Driver::class);
        $image = $manager->read($original_file_path); # read image from file system

        if ($width and $height) {
            if ($cut) {
                $image = $image->coverDown(width: $width, height: $height); # Cut image proportionally to size
            } else {
                $image = $image->scaleDown(width: $width, height: $height); # resize image proportionally to width
            }
        }

        // insert watermark
        if ($watermark) {
            $water_image    = $manager->read($watermark);
            $image_size     = $image->size();

            $watermark_offet_x = Settings::getParam('watermark_offset_x') ?? 0;
            $watermark_offet_y = Settings::getParam('watermark_offset_y') ?? 0;

            // Делаем Watermark меньше на 10% от изображения
            $water_image = $water_image->scaleDown(width: $image_size->width() - $image_size->width() * 0.1, height: $image_size->height() - $image_size->height() * 0.1);
            $water_image_size = $water_image->size();

            $offset_x = min(($image_size->width() - $water_image_size->width()) * $watermark_offet_x / 100, $image_size->width());
            $offset_y = min(($image_size->height() - $water_image_size->height()) * $watermark_offet_y / 100, $image_size->height());

            $image = $image->place($water_image, 'top-left', $offset_x, $offset_y, $watermark_transparency);
        }

        if ($sharpen > 0) {
            $image = $image->sharpen($sharpen);
        }

        if (strtolower($format) === 'webp') {

            // strip - удалить мета информацию EXIF
            $image = $image->toWebp($quality, strip: true);
        }

        $image->save($new_file_path); # save modified image in new file 

        return $new_file_path;
    }


    /**
     * Добавляем параметры размера, водяного знака
     */
    public static function addResizeParams(string $filename, int $width = 0, int $height = 0, bool $watermark = false, bool $cut = false, ?string $format = null): string
    {
        $pathinfo   = pathinfo($filename);

        $dirname    = $pathinfo['dirname'] ?? '';
        $basename   = $pathinfo['filename'] ?? '';
        $ext        = $format ?: $pathinfo['extension'] ?? '';

        $prefix     = ($dirname !== '.' && $dirname !== '') ? $dirname . '/' : '';
        $flags      = ($cut ? 'c' : '') . ($watermark ? 'w' : '');

        // Если указан хотя бы один размер — добавляем .WIDTHxHEIGHTFLAGS
        if ($width > 0 || $height > 0) {
            return "{$prefix}{$basename}." . ($width > 0 ? $width : '') . 'x' . ($height > 0 ? $height : '') . $flags . '.' . $ext;
        } else {

            // Только водяной знак без размеров
            return "{$prefix}{$basename}." . (str_contains($flags, 'w') ? 'w' . '.' : '') . $ext;
        }
    }


    /**
     * Разбирает параметры файла на парамеры.
     * Поддержка флагов 'c', 'w' в любом порядке (например: 'cw', 'wc')
     * @param string $filename
     */
    public static function getResizeParams(string $filename)
    {

        // Определаяем параметры ресайза
        if (!preg_match('/(.+)\.(\d*)x(\d*)([cw]{0,2})\.([a-z0-9]+)$/i', $filename, $matches)) {
            return false;
        }

        $file           = $matches[1];            # имя запрашиваемого файла
        $width          = $matches[2] ?: null;    # ширина будущего изображения
        $height         = $matches[3] ?: null;    # высота будущего изображения
        $flags          = $matches[4] ?? '';      # строка флагов
        $ext            = $matches[5];            # расширение файла

        // flags
        $set_watermark  = str_contains($flags, 'w');
        $cut            = str_contains($flags, 'c');

        return [$file, $ext, $width, $height, $set_watermark, $cut];
    }


    /**
     * Заливаем файл (оригинальные изображения) на сервер
     *
     * @param string $temp_filename
     * @param string $original_filename
     * @param $width
     * @param $height
     */
    public static function uploadImage(string $temp_filename, string $original_filename, ?int $width = null, ?int $height = null)
    {

        $original_filename  = Helper::slugEn($original_filename); # Lowcase EN charcters
        $root_name          = substr(pathinfo($original_filename, PATHINFO_FILENAME), 0, 16); # Max 16 characters
        $ext                = pathinfo($original_filename, PATHINFO_EXTENSION);
        $image_path         = Config::get('images_originals_dir') . $root_name . '.' . $ext;

        // Пропускаем только разрешенные расширения
        if (in_array(strtolower($ext), self::$allowed_extentions)) {

            // Если файл с таким именем уже существует, добавим token
            while (file_exists($image_path)) {

                // GoogleSearch likes '-'
                $image_path = Config::get('images_originals_dir') . $root_name . '-' . Helper::makeToken(uniqid(), 8) . '.' . $ext;
            }

            // Настройки взять в config
            $width  = $width ?: Config::get('images_max_size') ?? null;
            $height = $height ?: Config::get('images_max_size') ?? null;

            // Изменяем размер изображения
            if (!empty($image_path = self::resizeUploadImage($temp_filename, $image_path, $width, $height, sharpen: 0, format: null))) {
                return pathinfo($image_path, PATHINFO_BASENAME);
            }
        }
        return;
    }


    /**
     * Сlear image resize folder
     * @param ?string $dir
     */
    public static function clearImageResize(?string $dir = null)
    {
        $dir = $dir ?: Config::get('images_resized_dir');
        foreach (glob($dir . '/*') as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
