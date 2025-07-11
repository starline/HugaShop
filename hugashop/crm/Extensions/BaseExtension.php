<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;

class BaseExtension
{

    public static $class_name;
    public static $settings;       # extension settings
    public $config;                # extension config
    public $ext_env;

    public function __construct()
    {
        $this->config =      Helper::getModule(self::getName(), Config::get('extension_dir'));
    }


    /**
     * Get Extension name (Module)
     */
    public static function getName()
    {
        return static::$class_name ?: static::$class_name = class_basename(static::class);
    }


    /**
     * Get setting param
     */
    public static function getSettings(?string $param = null)
    {
        $settings = static::$settings ?: (object) (Settings::getParam(self::getName()) ?? []); # was array

        if (is_null($param)) {
            return $settings;
        }
        return $settings->$param ?? null;
    }


    /**
     * Get extension params
     */
    public function getExtension()
    {
        $extension = $this->getConfig();
        $extension->settings = self::getSettings();
        $extension->hasIndex = $this->hasIndex();
        return $extension;
    }


    /**
     * Has index function
     */
    public function hasIndex()
    {
        return method_exists($this, 'index') ? true : false;
    }


    /**
     * Set environment
     * @param string $env_name
     */
    public function setEnvironment(string $env_name, $env)
    {
        if (empty($this->ext_env)) {
            $this->ext_env = new \stdClass();
        }

        $this->ext_env->$env_name = $env;
    }


    /**
     * Get Extension directory
     */
    public function getExtensionDir()
    {
        return Config::get('extension_dir') . self::getName() . '/';
    }


    /**
     * Get Extension config
     */
    public function getConfig(?string $param = null)
    {
        if (is_null($param)) {
            return $this->config;
        }
        return $this->config->$param ?? null;
    }


    /**
     * Fetch extension template 
     * @param string $template
     */
    public function fetchTemplate(string $template)
    {
        return Design::fetch($this->getTemplatePath($template));
    }


    /**
     * Get extension template
     * @param string $template
     */
    public function getTemplatePath(string $template)
    {
        return $this->getExtensionDir() . $template;
    }


    /**
     * Ajax. Update Model. Clear cache
     * Make: HugaShop\Extensions\InfoBlock\Models\InfoBlock
     * From: HugaShop\Extensions\InfoBlock\InfoBlock
     */
    public function updateOne($id, $entity)
    {
        // Main Model is always ClassName . Model
        $class_name         = self::getName();
        $base_namespace     = preg_replace('/\\\\' . preg_quote($class_name, '/') . '$/', '', static::class);
        $class              = $base_namespace . '\\Models\\' . $class_name;

        $class::updateOne($id, $entity);
        $class::cacheClear();
    }
}
