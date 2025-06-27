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
    public $config;         # extension config
    public $settings;       # extension settings
    public $class_name;
    public $ext_env;

    public function __construct()
    {
        $this->class_name =         Helper::class_basename(static::class);
        $this->settings =           (object) (Settings::getParam($this->class_name) ?? []); # was array
        $this->config =             Helper::getModule($this->class_name, Config::get('extension_dir'));
    }


    /**
     * Get extension params
     */
    public function getExtension()
    {
        $extension = $this->getConfig();
        $extension->settings = $this->getSetting();
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
     * Get Extension name (Module)
     */
    public function getName()
    {
        return $this->class_name;
    }


    /**
     * Get Extension directory
     */
    public function getExtensionDir()
    {
        return Config::get('extension_dir') . $this->class_name . '/';
    }


    /**
     * Get setting param
     */
    public function getSetting(?string $param = null)
    {
        if (is_null($param)) {
            return $this->settings;
        }
        return $this->settings->$param ?? null;
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
     * Ajax
     * Make: HugaShop\Extensions\InfoBlock\Models\InfoBlock
     * From: HugaShop\Extensions\InfoBlock\InfoBlock
     */
    public function updateOne($id, $entity)
    {
        // Main Model is always ClassName . Model
        $full_class         = static::class;
        $class_name         =  Helper::class_basename($full_class);
        $base_namespace     = preg_replace('/\\\\' . preg_quote($class_name, '/') . '$/', '', $full_class);
        $class              = $base_namespace . '\\Models\\' . $class_name;

        $class::updateOne($id, $entity);
        Helper::cache(static::class)->clear();
    }
}
