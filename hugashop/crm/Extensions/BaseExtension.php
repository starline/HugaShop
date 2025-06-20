<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Extensions;

use HugaShop\Api\Config;
use HugaShop\Api\Design;
use HugaShop\Api\Helper;
use HugaShop\Api\Settings;

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
     * Set enviroment
     * @param string $env_name
     */
    public function setEnviroment(string $env_name, $env)
    {
        $this->ext_env->$env_name = $env;
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
}
