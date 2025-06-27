<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Models\Config;
use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Settings;

class BaseExtension
{
    public $ext_config;     # extension config
    public $ext_settings;   # extension settings
    public $ext_name;
    public $ext_dir;
    public $ext_env;

    public function __construct()
    {
        $this->ext_name =           Helper::class_basename(static::class);
        $this->ext_settings =       (object) Settings::getParam($this->ext_name); # was array
        $this->ext_config =         Helper::getModule($this->ext_name, Config::get('extension_dir'));
        $this->ext_dir =            Config::get('extension_dir') . $this->ext_name . '/';
        $this->ext_env =            new \stdClass();
    }


    /**
     * Set environment
     * @param string $env_name
     */
    public function setEnvironment(string $env_name, $env)
    {
        $this->ext_env->$env_name = $env;
    }


    /**
     * Get Extension name (Module)
     */
    public function getName()
    {
        return $this->ext_name;
    }


    /**
     * Get setting param
     */
    public function getSetting(?string $param = null)
    {
        if (is_null($param)) {
            return $this->ext_settings;
        }
        return $this->ext_settings->$param ?? null;
    }


    /**
     * Get Extension config
     */
    public function getConfig(?string $param = null)
    {
        if (is_null($param)) {
            return $this->ext_config;
        }
        return $this->ext_config->$param ?? null;
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
        return $this->ext_dir . $template;
    }


    /**
     * Ajax
     * Make: HugaShop\Extensions\InfoBlock\Models\InfoBlock
     * From: HugaShop\Extensions\InfoBlock\InfoBlock
     */
    public function updateOne($id, $entity)
    {
        // Main Model is always ClassName . Model
        $full_class = static::class;
        $class_name =  Helper::class_basename($full_class);
        $base_namespace = preg_replace('/\\\\' . preg_quote($class_name, '/') . '$/', '', $full_class);
        $class = $base_namespace . '\\Models\\' . $class_name;
        $class::updateOne($id, $entity);
        Helper::cache(static::class)->clear();
    }
}
