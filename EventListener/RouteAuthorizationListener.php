<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;

class RouteAuthorizationListener {
  protected $laRoute;
  protected $userHelper;

  public function __construct(LogicalAuthorizationRouteInterface $laRoute) {
    $this->laRoute = $laRoute;
  }

  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    $routeName = $request->get('_route');
    if($routeName) {
      if(!$this->laRoute->checkRouteAccess($routeName)) {
        throw new AccessDeniedException();
      }
    }
  }
}