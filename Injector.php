<?php

/**
 * This file is part of the Vection package.
 *
 * (c) David M. Lung <vection@davidlung.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Vection\Component\DI;

use ArrayObject;

/**
 * Class Injector
 *
 * @package Vection\Component\DI
 *
 * @author  David M. Lung <vection@davidlung.de>
 */
class Injector
{

    /** @var Container */
    protected $container;

    /**
     * @var ArrayObject
     */
    protected $dependencies;

    /**
     * Resolver constructor.
     *
     * @param Container $container
     * @param ArrayObject $dependencies
     */
    public function __construct(Container $container, ArrayObject $dependencies)
    {
        $this->container    = $container;
        $this->dependencies = $dependencies;
    }

    /**
     * Injects all dependencies defined by the given object.
     *
     * @param object $object
     */
    public function injectDependencies(object $object): void
    {
        $this->injectByInterface($object);
        $this->injectByAnnotations($object);
        $this->injectByExplicit($object);

        # If object uses ContainerAwareTrait, inject the container itself
        if ( method_exists($object, '__setContainer') ) {
            $object->__setContainer($this->container);
        }
    }

    /**
     * @param object $object
     */
    protected function injectByInterface(object $object): void
    {
        $id = get_class($object);

        if ( ($this->dependencies[$id]['setter'] ?? null) ) {

            foreach ( $this->dependencies[$id]['setter'] as $setter => $dependency ) {
                $dependencyObject = $this->container->get($dependency);
                $object->$setter($dependencyObject);
            }

        }
    }

    /**
     * @param object $object
     */
    protected function injectByAnnotations(object $object): void
    {
        $id = get_class($object);

        if ( ($this->dependencies[$id]['annotation'] ?? null) ) {

            $dependencies = [];

            foreach ( $this->dependencies[$id]['annotation'] as $property => $dependency ) {
                $dependencies[$property] = $this->container->get($dependency);
            }

            $object->__annotationInjection($dependencies);
        }
    }

    /**
     * @param object $object
     */
    protected function injectByExplicit(object $object): void
    {
        $id = get_class($object);

        if ( ($this->dependencies[$id]['explicit'] ?? null) ) {

            $dependencies = [];

            foreach ( $this->dependencies[$id]['explicit'] as $dependency ) {
                $dependencies[] = $this->container->get($dependency);
            }

            $object->__inject(...$dependencies);
        }
    }
}
