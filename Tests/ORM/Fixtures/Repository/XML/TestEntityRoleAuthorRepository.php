<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Fixtures\Repository\XML;

/**
 * TestEntityRoleAuthorRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TestEntityRoleAuthorRepository extends \Doctrine\ORM\EntityRepository
{
  public function customMethod() {
    return $this->findAll();
  }
}