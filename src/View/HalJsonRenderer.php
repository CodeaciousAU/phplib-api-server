<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\View;

use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;

class HalJsonRenderer implements RendererInterface
{
    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param ResolverInterface $resolver
     * @return void
     */
    public function setResolver(ResolverInterface $resolver)
    {
        //Resolver is not required by this renderer
    }

    /**
     * {@inheritdoc}
     *
     * @param string|\Laminas\View\Model\ModelInterface $nameOrModel
     * @param null|array|\ArrayAccess $values
     * @return string
     */
    public function render($nameOrModel, $values = null)
    {
        if (! $nameOrModel instanceof HalJsonModel)
            throw new \RuntimeException(__CLASS__.' is unable to render this view model type');

        return $nameOrModel->serialize();
    }
}