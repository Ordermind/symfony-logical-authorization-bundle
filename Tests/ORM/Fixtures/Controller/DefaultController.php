<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use AppBundle\Entity\TestEntity;

class DefaultController extends Controller {

  /**
    * @Route("/count-unknown-result-roleauthor", name="count_unknown_result_roleauthor")
    * @Method({"GET"})
    */
  public function countUnknownResultRoleAuthorAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
    $result = $operations->getUnknownResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-unknown-result-hasaccount", name="count_unknown_result_hasaccount")
    * @Method({"GET"})
    */
  public function countUnknownResultHasAccountAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
    $result = $operations->getUnknownResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-unknown-result-nobypass", name="count_unknown_result_nobypass")
    * @Method({"GET"})
    */
  public function countUnknownResultNoBypassAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
    $result = $operations->getUnknownResult();
    return new Response(count($result));
  }

  /**
    * @Route("/find-single-model-result-roleauthor/{id}", name="find_single_model_result_roleauthor")
    * @Method({"GET"})
    */
  public function findSingleModelResultRoleAuthorAction(Request $request, $id) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
    $result = $operations->getSingleModelResult($id);
    return new JsonResponse((bool) $result);
  }

  /**
    * @Route("/find-single-model-result-hasaccount/{id}", name="find_single_model_result_hasaccount")
    * @Method({"GET"})
    */
  public function findSingleModelResultHasAccountAction(Request $request, $id) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
    $result = $operations->getSingleModelResult($id);
    return new JsonResponse((bool) $result);
  }

  /**
    * @Route("/find-single-model-result-nobypass/{id}", name="find_single_model_result_nobypass")
    * @Method({"GET"})
    */
  public function findSingleModelResultNoBypassAction(Request $request, $id) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
    $result = $operations->getSingleModelResult($id);
    return new JsonResponse((bool) $result);
  }

  /**
    * @Route("/count-multiple-model-result-roleauthor", name="count_multiple_model_result_roleauthor")
    * @Method({"GET"})
    */
  public function countMultipleModelResultRoleAuthorAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
    $result = $operations->getMultipleModelResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-multiple-model-result-hasaccount", name="count_multiple_model_result_hasaccount")
    * @Method({"GET"})
    */
  public function countMultipleModelResultHasAccountAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
    $result = $operations->getMultipleModelResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-multiple-model-result-nobypass", name="count_multiple_model_result_nobypass")
    * @Method({"GET"})
    */
  public function countMultipleModelResultNoBypassAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
    $result = $operations->getMultipleModelResult();
    return new Response(count($result));
  }

  /**
    * @Route("/count-entities-lazy-roleauthor", name="test_count_entities_lazy_roleauthor")
    * @Method({"GET"})
    */
  public function countEntitiesLazyLoadRoleAuthorAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
    $collection = $operations->getLazyLoadedModelResult();
    return new Response(count($collection));
  }

  /**
    * @Route("/count-entities-lazy-hasaccount", name="test_count_entities_lazy_hasaccount")
    * @Method({"GET"})
    */
  public function countEntitiesLazyLoadHasAccountAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
    $collection = $operations->getLazyLoadedModelResult();
    return new Response(count($collection));
  }

  /**
    * @Route("/count-entities-lazy-nobypass", name="test_count_entities_lazy_nobypass")
    * @Method({"GET"})
    */
  public function countEntitiesLazyLoadNoBypassAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
    $collection = $operations->getLazyLoadedModelResult();
    return new Response(count($collection));
  }

  /**
    * @Route("/create-entity-roleauthor", name="create_entity_roleauthor")
    * @Method({"POST"})
    */
  public function createEntityRoleAuthorAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
    $modelManager = $operations->createTestModel($user);
    return new JsonResponse((bool) $modelManager);
  }

  /**
    * @Route("/create-entity-hasaccount", name="create_entity_hasaccount")
    * @Method({"POST"})
    */
  public function createEntityHasAccountAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
    $modelManager = $operations->createTestModel($user);
    return new JsonResponse((bool) $modelManager);
  }

  /**
    * @Route("/create-entity-nobypass", name="create_entity_nobypass")
    * @Method({"POST"})
    */
  public function createEntityNoBypassAction(Request $request) {
    $user = $this->get('ordermind_logical_authorization.service.user_helper')->getCurrentUser();
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
    $modelManager = $operations->createTestModel($user);
    return new JsonResponse((bool) $modelManager);
  }
}
