<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */
namespace Codeacious\ApiServer;

/**
 * The module class for the ApiServer module.
 */
class Module
{
    /**
     * @return array|null
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }
}
