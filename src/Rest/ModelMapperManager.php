<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Rest;

use Laminas\ServiceManager\AbstractPluginManager;

class ModelMapperManager extends AbstractPluginManager
{
    protected $instanceOf = ModelMapper::class;
}