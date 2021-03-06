<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

/**
 * {@inheritdoc}
 */
class LogicalAuthorization implements LogicalAuthorizationInterface
{
    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface
     */
    protected $lpProxy;

    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\HelperInterface
     */
    protected $helper;

    /**
     * @internal
     *
     * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface $lpProxy             The logical permissions proxy to use
     * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface                  $helper              LogicalAuthorization helper service
     * @param Ordermind\LogicalPermissions\BypassAccessCheckerInterface                      $bypassAccessChecker The bypass access checker to use
     */
    public function __construct(LogicalPermissionsProxyInterface $lpProxy, HelperInterface $helper, BypassAccessCheckerInterface $bypassAccessChecker)
    {
        $this->lpProxy = $lpProxy;
        if (!$this->lpProxy->getBypassAccessChecker()) {
            $this->lpProxy->setBypassAccessChecker($bypassAccessChecker);
        }
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($permissions, array $context, bool $allowBypass = true): bool
    {
        try {
            return $this->lpProxy->checkAccess($permissions, $context, $allowBypass);
        } catch (\Exception $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $this->helper->handleError("An exception was caught while checking access: \"$message\" at ".$e->getFile()." line ".$e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
        }

        return false;
    }
}
