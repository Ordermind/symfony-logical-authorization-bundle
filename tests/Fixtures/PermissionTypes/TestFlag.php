<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionTypes;

use Ordermind\LogicalAuthorizationBundle\PermissionType\Flag\FlagInterface;

class TestFlag implements FlagInterface
{
    /**
     * @var string|null
     */
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkFlag(array $context): bool
    {
        return true;
    }
}
