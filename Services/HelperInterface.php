<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface HelperInterface {

  /**
   * Gets the current user
   *
   * @return mixed If the current user is authenticated the user object is returned. If the current user is anonymous the string "anon." is returned. If no current user can be found, NULL is returned.
   */
  public function getCurrentUser();

  /**
   * @internal Logs an error if a logging service is available. Otherwise it outputs the error as a Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException.
   *
   * @param string $message The error message
   * @param array $context The context for the error
   */
  public function handleError($message, $context);
}
