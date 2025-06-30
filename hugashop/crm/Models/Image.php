<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 * 
 * Intervention Image
 * @link https://image.intervention.io/v3/getting-started/installation
 *
 */

namespace HugaShop\Models;

use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class Image extends BaseModel
{

    protected $table = 'content_image';

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar'],
        'entity_id' =>          ['type' => 'int'],
        'entity_name' =>        ['type' => 'varchar',       'lenght' => 25],
        'filename' =>           ['type' => 'varchar'],
        'position' =>           ['type' => 'created',       'def' => 0],
        'created' =>            ['type' => 'varchar',       'def' => 'CURRENT_TIMESTAMP']
    ];


    private static $allowed_extentions = ['png', 'gif', 'jpg', 'jpeg', 'ico', 'webp', 'bmp'];
    public static $token_lenght = 10;

    public function entity()
    {
        return $this->morphTo();
    }


    /**
     * Catch image from POST
     */
    public static function catchImages($entity_id, $entity_name, $post_name = 'images')
    {
        // Удаление основных изображений
        $images = Request::post($post_name, 'array') ?: [];
        $current_images = self::getImages($entity_id, $entity_name);

        foreach ($current_images as $image) {
            if (!in_array($image->id, $images, true)) {
                self::deleteImage($image->id);
            }
        }

        // Порядок основных изображений
        foreach ($images as $position => $im_id) {
            self::updateOne($im_id, ['position' => $position]);
        }

        // Загрузка основных изображений из интернета и drag-n-drop файлов
        if ($urls = Request::post($post_name . '_urls')) {
            $dropped_images = Request::files('dropped_' . $post_name);
            foreach ($urls as $url) {
                // Если не пустой адрес и файл не локальный
                if (!empty($url) && $url !== 'http://' && str_contains($url, '/')) {
                    self::addImage($entity_id, $entity_name, $url);
                } elseif ($dropped_images) {
                    $key = array_search($url, $dropped_images['name'], true);
                    if ($key !== false) {
                        // Move and Resize
                        $image_name = self::uploadImage($dropped_images['tmp_name'][$key], $dropped_images['name'][$key]);
                        if ($image_name) {
                            self::addImage($entity_id, $entity_name, (string) $image_name);
                        }
                    }
                }
            }
        }
    }


    /**
     * Get images
     * @param int|array $entity_id
     * @param string $entity_name - product
     */
    public static function getImages(int|array $entity_id, string $entity_name)
    {
        return self::getList(filter: ['entity_id' => $entity_id, 'entity_name' => $entity_name], order: 'position');
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

        // Обновляем позицию = id
        $image->position = $image->id;
        $image->save();

        return $image->id;
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
     * Making Image URL
     * @param $filename
     * @param ?string $flags w - watermark, c - cut for size
     */
    public static function getImageURL($filename, $width = 0, $height = 0, string $flags = '')
    {
        $watermark  = str_contains($flags, 'w');
        $cut        = str_contains($flags, 'c');

        $resized_filename = self::addResizeParams($filename, $width, $height, $watermark, $cut);
        $resized_filename_encoded = $resized_filename;

        if (substr($resized_filename_encoded, 0, 7) == 'http://' || substr($resized_filename_encoded, 0, 8) == 'https://') {
            $resized_filename_encoded = rawurlencode($resized_filename_encoded);
        }

        $resized_filename_encoded = rawurlencode($resized_filename_encoded);
        return Config::get('root_url') . '/' . Config::get('images_resized_url') . $resized_filename_encoded . '?' . Helper::makeToken($resized_filename, self::$token_lenght);
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
    public static function resize($filename)
    {

        $params = self::getResizeParams($filename);
        if (!$params) {
            return false;
        }
        list($source_file, $width, $height, $set_watermark, $cut) = $params;


        // Если файл удаленный (http://|https://), зальем его себе
        if (substr($source_file, 0, 7) == 'http://' || substr($source_file, 0, 8) == 'https://') {

            // Имя оригинального файла
            if (!$original_file = self::downloadImage($source_file)) {
                return false;
            }
        } else {
            $original_file = $source_file;
        }


        $resized_file = self::addResizeParams($original_file, $width, $height, $set_watermark, $cut);

        // Пути к папкам с картинками
        $originals_dir  = Config::get('images_originals_dir');
        $resized_dir    = Config::get('images_resized_dir');

        // Make resize dir
        if (!is_dir($resized_dir)) {
            mkdir($resized_dir, 0777, true);
        }

        // Проверяем что файл существует
        if (!is_file($originals_dir . $original_file)) {
            return false;
        }

        // Get watermarck image
        $watermark = null;
        if ($set_watermark && is_file(Config::get('images_watermark_file'))) {
            $watermark = Config::get('images_watermark_file');
        }

        $original_file_path = $originals_dir . $original_file;
        $new_file_path      = $resized_dir . $resized_file;

        return self::resizeUploadImage($original_file_path, $new_file_path, $width, $height, $watermark, $cut);
    }


    /**
     * Ресайз загруженых изображений
     */
    public static function resizeUploadImage(string $original_file_path, string $new_file_path, $width = null, $height = null, $watermark = false, $cut = false)
    {

        $quality = Config::get('images_quality');

        // Четкость изображения. 0-100 (больше - четче)
        $sharpen = min(100, (int)Settings::getParam('images_sharpen')) / 100;

        // Прозрачность водяного знака. Настройки сайта.
        $watermark_transparency = min(100, (int)Settings::getParam('watermark_transparency'));

        // create image manager with desired driver
        $manager = new ImageManager(Driver::class);

        $image = $manager->read($original_file_path); # read image from file system

        if (!empty($width) and !empty($height) and $cut) {
            $image = $image->coverDown(width: $width, height: $height); # Cut image proportionally to size
        } else {
            $image = $image->scaleDown(width: $width, height: $height); # resize image proportionally to width
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

        if (!empty($sharpen)) {
            $image = $image->sharpen($sharpen);
        }

        $image->toWebp($quality)->save($new_file_path); # save modified image in new format 

        return $new_file_path;
    }


    /**
     * Добавляем параметры размера, водяного знака
     */
    public static function addResizeParams($filename, $width = 0, $height = 0, $watermark = false, $cut = false)
    {
        $pathinfo   = pathinfo($filename);

        $dirname    = $pathinfo['dirname'] ?? '';
        $basename   = $pathinfo['filename'] ?? '';
        $ext        = $pathinfo['extension'] ?? '';

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

        $file_name = $file . '.' . $ext;
        return [$file_name, $width, $height, $set_watermark, $cut];
    }


    /**
     * Заливаем файл на сервер по http://
     * @param string $filename
     */
    public static function downloadImage(string $filename)
    {

        // Заливаем только если такой файл есть в базе
        if (!self::where('filename', $filename)->exists()) {
            return false;
        }

        // Имя оригинального файла
        $basename =         explode('&', pathinfo($filename, PATHINFO_BASENAME));
        $uploaded_file =    array_shift($basename);  # first
        $root_name =        urldecode(pathinfo($uploaded_file, PATHINFO_FILENAME));
        $ext =              pathinfo($uploaded_file, PATHINFO_EXTENSION);

        // Если такой файл существует, нужно придумать другое название
        $image_path = Config::get('images_originals_dir') . $root_name . '.' . $ext;
        while (file_exists($image_path)) {

            // Google likes '-'
            $image_path = Config::get('images_originals_dir') . $root_name . '-' . Helper::makeToken(uniqid(), 8) . '.' . $ext;
        }

        $new_name =  pathinfo($image_path, PATHINFO_BASENAME);

        // Перед долгим копированием займем это имя
        self::where('filename', $filename)->update(['filename' => $new_name]);

        fclose(fopen($image_path, 'w'));
        copy($filename, $image_path);

        return $new_name;
    }


    /**
     * Заливаем файл (оригинальные изображения) на сервер
     *
     * @param string $temp_filename
     * @param string $original_filename
     * @param $width
     * @param $height
     * @return $image_name
     */
    public static function uploadImage(string $temp_filename, string $original_filename, ?int $width = null, ?int $height = null)
    {

        $original_filename =    Helper::slugEn($original_filename); # Lowcase EN charcters
        $root_name =            substr(pathinfo($original_filename, PATHINFO_FILENAME), 0, 16); # Max 16 characters
        $ext =                  pathinfo($original_filename, PATHINFO_EXTENSION);
        $image_path =           Config::get('images_originals_dir') . $root_name . '.' . $ext;

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
            if (!empty($image_path = self::resizeUploadImage($temp_filename, $image_path, $width, $height))) {
                return pathinfo($image_path, PATHINFO_BASENAME);
            }
        }

        return null;
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
