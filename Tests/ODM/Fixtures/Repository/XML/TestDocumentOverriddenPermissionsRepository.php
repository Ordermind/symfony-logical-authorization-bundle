<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Fixtures\Repository\XML;

/**
 * TestDocumentOverriddenPermissionsRepository
 *
 * This class was generated by the Doctrine ODM. Add your own custom
 * repository methods below.
 */
class TestDocumentOverriddenPermissionsRepository extends \Doctrine\ODM\MongoDB\DocumentRepository
{
  public function customMethod() {
    return $this->findAll();
  }
}
