<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Routing\Route as RouteBase;
use TypeError;

/**
 * {@inheritDoc}
 */
class AnnotationClassLoader extends AnnotatedRouteControllerLoader
{
    /**
     * {@inheritDoc}
     */
    protected function configureRoute(RouteBase $route, ReflectionClass $class, ReflectionMethod $method, $annot)
    {
        if (!($route instanceof RouteInterface)) {
            throw new TypeError('The route parameter must implement RouteInterface.');
        }

        parent::configureRoute($route, $class, $method, $annot);
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof Permissions) {
                $route->setPermissions($configuration->getPermissions());
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function createRoute(
        $path,
        $defaults,
        $requirements,
        $options,
        $host,
        $schemes,
        $methods,
        $condition
    ): Route {
        return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }
}
