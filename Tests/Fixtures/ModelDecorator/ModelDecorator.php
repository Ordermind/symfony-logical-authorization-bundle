<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\ModelDecorator;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;

class ModelDecorator implements ModelDecoratorInterface
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }
}
