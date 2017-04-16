<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\UserHelperInterface;

class LogicalAuthorizationModel implements LogicalAuthorizationModelInterface {

  protected $la;
  protected $treeManager;
  protected $userHelper;

  public function __construct(LogicalAuthorizationInterface $la, PermissionTreeManagerInterface $treeManager, UserHelperInterface $userHelper) {
    $this->la = $la;
    $this->treeManager = $treeManager;
    $this->userHelper = $userHelper;
  }

  public function getAllAvailableActions($model, $user = null, $model_actions = array('create', 'read', 'update', 'delete'), $field_actions = array('get', 'set')) {
    $available_actions = [];
    $model = $this->la->getRidOfManager($model);
    foreach($model_actions as $action) {
      if($this->checkModelAccess($model, $action, $user)) {
        $available_actions[$action] = true;
      }
    }
    $reflectionClass = new \ReflectionClass($model);
    foreach($reflectionClass->getProperties() as $property) {
      $field_name = $property->getName();
      foreach($field_actions as $action) {
        if($this->checkFieldAccess($model, $field_name, $action, $user)) {
          if(!isset($available_actions['fields'])) $available_actions['fields'] = [];
          if(!isset($available_actions['fields'][$field_name])) $available_actions['fields'][$field_name] = [];
          $available_actions['fields'][$field_name][$action] = true;
        }
      }
    }
    return $available_actions;
  }

  public function checkModelAccess($model, $action, $user = null) {
    $model = $this->la->getRidOfManager($model);
    if(is_null($user)) {
      $user = $this->userHelper->getCurrentUser();
      if(is_null($user)) return true;
    }
    $user = $this->la->getRidOfManager($user);

    if(!is_string($model) && !is_object($model)) {
      $this->la->handleError('Error checking model access: the model parameter must be either a class string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($action)) {
      $this->la->handleError('Error checking model access: the action parameter must be a string.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->la->handleError('Error checking model access: the user parameter must be either a string or an object.', ['model' => $model, 'action' => $action, 'user' => $user]);
      return false;
    }

    $permissions = $this->getModelPermissions($model);
    if(array_key_exists($action, $permissions)) {
      $context = ['model' => $model, 'user' => $user];
      return $this->la->checkAccess($permissions[$action], $context);
    }
    return true;
  }

  public function checkFieldAccess($model, $field_name, $action, $user = null) {
    $model = $this->la->getRidOfManager($model);
    if(is_null($user)) {
      $user = $this->userHelper->getCurrentUser();
      if(is_null($user)) return true;
    }
    $user = $this->la->getRidOfManager($user);

    if(!is_string($model) && !is_object($model)) {
      $this->la->handleError('Error checking field access: the model parameter must be either a class string or an object.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($field_name)) {
      $this->la->handleError('Error checking field access: the field_name parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($action)) {
      $this->la->handleError('Error checking field access: the action parameter must be a string.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }
    if(!is_string($user) && !is_object($user)) {
      $this->la->handleError('Error checking field access: the user parameter must be either a string or an object.', ['model' => $model, 'field name' => $field_name, 'action' => $action, 'user' => $user]);
      return false;
    }

    $permissions = $this->getModelPermissions($model);
    if(!empty($permissions['fields'][$field_name]) && array_key_exists($action, $permissions['fields'][$field_name])) {
      $context = ['model' => $model, 'user' => $user];
      return $this->la->checkAccess($permissions['fields'][$field_name][$action], $context);
    }
    return true;
  }

  protected function getModelPermissions($model) {
    $tree = $this->treeManager->getTree();
    $psr_class = '';
    if(is_string($model)) {
      $psr_class = $model;
    }
    elseif(is_object($model)) {
      $psr_class = get_class($model);
    }

    if(!empty($tree['models']) && array_key_exists($psr_class, $tree['models'])) {
      return $tree['models'][$psr_class];
    }
    return [];
  }
}