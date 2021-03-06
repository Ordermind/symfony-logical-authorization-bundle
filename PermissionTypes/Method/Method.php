<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Method;

use Symfony\Component\HttpFoundation\RequestStack;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

/**
 * Permission type for checking http method
 */
class Method implements PermissionTypeInterface
{
    protected $requestStack;

    /**
     * @internal
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack RequestStack service for fetching the current request
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'method';
    }

    /**
     * Checks if the current request uses an allowed method
     *
     * @param string $method  The method to evaluate
     * @param array  $context The context for evaluating the method
     *
     * @return bool TRUE if the method is allowed or FALSE if it is not allowed
     */
    public function checkPermission($method, $context)
    {
        if (!is_string($method)) {
            throw new \TypeError('The method parameter must be a string.');
        }
        if (!$method) {
            throw new \InvalidArgumentException('The method parameter cannot be empty.');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        if (!$currentRequest) {
            return false;
        }

        return strcasecmp($currentRequest->getMethod(), $method) == 0;
    }
}
