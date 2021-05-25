<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\View;

use Laminas\View\Model\JsonModel;
use Nocarrier\Hal;

class HalJsonModel extends JsonModel
{
    /**
     * @var Hal
     */
    protected $hal;

    /**
     * @param Hal $hal
     */
    public function __construct(Hal $hal)
    {
        $this->hal = $hal;
        parent::__construct();
    }

    /**
     * @return Hal
     */
    public function getHal()
    {
        return $this->hal;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return $this->hal->asJson();
    }
}