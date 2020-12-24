<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker;

interface SimpleConditionCheckerInterface
{
    /**
     * Gets the name of the condition that this class checks.
     */
    public static function getName(): string;

    /**
     * Checks the condition.
     */
    public function checkCondition(array $context): bool;
}
