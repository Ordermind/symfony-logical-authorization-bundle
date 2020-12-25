<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Annotation\Routing;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\ValueObjects\RawPermissionTree;

/**
 * @Annotation
 */
class Permissions
{
    protected RawPermissionTree $rawPermissionTree;

    public function __construct(array $data)
    {
        if (!array_key_exists('value', $data)) {
            throw new InvalidArgumentException('The data parameter must have a "value" key');
        }

        $this->rawPermissionTree = new RawPermissionTree($data['value']);
    }

    /**
     * Gets the unvalidated permission tree for this route.
     *
     * @return RawPermissionTree
     */
    public function getRawPermissionTree()
    {
        return $this->rawPermissionTree;
    }
}
