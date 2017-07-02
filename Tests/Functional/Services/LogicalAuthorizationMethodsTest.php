<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Functional\Services;

use Symfony\Component\Routing\Route;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxy;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorization;
use Ordermind\LogicalAuthorizationBundle\Services\Helper;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\BypassAccess as BypassAccessFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\HasAccount as HasAccountFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\IsAuthor as IsAuthorFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagManager;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\ErroneousUser;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestUser;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\ErroneousModel;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestModelBoolean;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes\TestFlag;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Role\Role;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes\TestType;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;

class LogicalAuthorizationMethodsTest extends LogicalAuthorizationBase {

  /*------------ Permission types ---------------*/

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessWrongContextType() {
    $flag = new BypassAccessFlag();
    $flag->checkFlag(null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessMissingUser() {
    $flag = new BypassAccessFlag();
    $flag->checkFlag([]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagBypassAccessWrongUserType() {
    $flag = new BypassAccessFlag();
    $flag->checkFlag(['user' => []]);
  }

  /**
    * @expectedException UnexpectedValueException
    */
  public function testFlagBypassAccessWrongReturnType() {
    $user = new ErroneousUser();
    $flag = new BypassAccessFlag();
    $flag->checkFlag(['user' => $user]);
  }

  public function testFlagBypassAccessAnonymousUserDisallow() {
    $flag = new BypassAccessFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
  }

  public function testFlagBypassAccessDisallow() {
    $user = new TestUser();
    $flag = new BypassAccessFlag();
    $this->assertFalse($flag->checkFlag(['user' => $user]));
  }

  public function testFlagBypassAccessAllow() {
    $user = new TestUser();
    $user->setBypassAccess(true);
    $flag = new BypassAccessFlag();
    $this->assertTrue($flag->checkFlag(['user' => $user]));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagHasAccountWrongContextType() {
    $flag = new HasAccountFlag();
    $flag->checkFlag(null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagHasAccountMissingUser() {
    $flag = new HasAccountFlag();
    $flag->checkFlag([]);
  }

  public function testFlagHasAccountDisallow() {
    $flag = new HasAccountFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.']));
  }

  public function testFlagHasAccountAllow() {
    $user = new TestUser();
    $flag = new HasAccountFlag();
    $this->assertTrue($flag->checkFlag(['user' => $user]));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorWrongContextType() {
    $flag = new IsAuthorFlag();
    $flag->checkFlag(null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorMissingUser() {
    $flag = new IsAuthorFlag();
    $flag->checkFlag([]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorWrongUserType() {
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => []]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorMissingModel() {
    $user = new TestUser();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user]);
  }

  public function testFlagIsAuthorModelClassString() {
    $user = new TestUser();
    $flag = new IsAuthorFlag();
    $this->assertFalse($flag->checkFlag(['user' => $user, 'model' => 'Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Model\TestUser']));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorWrongModelType() {
    $user = new TestUser();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user, 'model' => []]);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagIsAuthorModelWrongAuthorType() {
    $user = new TestUser();
    $model = new ErroneousModel();
    $flag = new IsAuthorFlag();
    $flag->checkFlag(['user' => $user, 'model' => $model]);
  }

  public function testFlagIsAuthorModelAnonymousUserDisallow() {
    $model = new TestModelBoolean();
    $flag = new IsAuthorFlag();
    $this->assertFalse($flag->checkFlag(['user' => 'anon.', 'model' => $model]));
  }

  public function testFlagIsAuthorModelAnonymousAuthorDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $flag = new IsAuthorFlag();
    $this->assertFalse($flag->checkFlag(['user' => $user, 'model' => $model]));
  }

  public function testFlagIsAuthorModelAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $model->setAuthor($user);
    $flag = new IsAuthorFlag();
    $this->assertTrue($flag->checkFlag(['user' => $user, 'model' => $model]));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerAddFlagWrongNameType() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName(true);
    $flagManager->addFlag($flag);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerAddFlagEmptyName() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('');
    $flagManager->addFlag($flag);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerAddFlagAlreadyRegistered() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flagManager->addFlag($flag);
  }

  public function testFlagManagerAddFlag() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flags = $flagManager->getFlags();
    $this->assertTrue(isset($flags['test']));
    $this->assertSame($flag, $flags['test']);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerRemoveFlagWrongNameType() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flagManager->removeFlag(true);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerRemoveFlagEmptyName() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flagManager->removeFlag('');
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerRemoveFlagNotRegistered() {
    $flagManager = new FlagManager();
    $flagManager->removeFlag('test');
  }

  public function testFlagManagerRemoveFlag() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $flags = $flagManager->getFlags();
    $this->assertTrue(isset($flags['test']));
    $flagManager->removeFlag('test');
    $flags = $flagManager->getFlags();
    $this->assertFalse(isset($flags['test']));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerCheckPermissionWrongNameType() {
    $flagManager = new FlagManager();
    $flagManager->checkPermission(true, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerCheckPermissionEmptyName() {
    $flagManager = new FlagManager();
    $flagManager->checkPermission('', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testFlagManagerCheckPermissionNotRegistered() {
    $flagManager = new FlagManager();
    $flagManager->checkPermission('test', []);
  }

  public function testFlagManagerCheckPermission() {
    $flagManager = new FlagManager();
    $flag = new TestFlag();
    $flag->setName('test');
    $flagManager->addFlag($flag);
    $this->assertTrue($flagManager->checkPermission('test', []));
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongRoleType() {
    $role = new Role();
    $role->checkPermission(true, []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleEmptyRole() {
    $role = new Role();
    $role->checkPermission('', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongContextType() {
    $role = new Role();
    $role->checkPermission('ROLE_USER', null);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleMissingUser() {
    $role = new Role();
    $role->checkPermission('ROLE_USER', []);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testRoleWrongUserType() {
    $role = new Role();
    $role->checkPermission('ROLE_USER', ['user' => []]);
  }

  public function testRoleAnonymousUserDisallow() {
    $role = new Role();
    $this->assertFalse($role->checkPermission('ROLE_USER', ['user' => 'anon.']));
  }

  public function testRoleDisallow() {
    $user = new TestUser();
    $role = new Role();
    $this->assertFalse($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
  }

  public function testRoleAllow() {
    $user = new TestUser();
    $role = new Role();
    $this->assertTrue($role->checkPermission('ROLE_USER', ['user' => $user]));
    $user->setRoles(['ROLE_ADMIN']);
    $this->assertTrue($role->checkPermission('ROLE_ADMIN', ['user' => $user]));
  }

  /*------------ Services -------------*/

  public function testHelperCurrentUser() {
    $this->sendRequestAs('GET', '/test/get-current-username', [], static::$authenticated_user);
    $response = $this->client->getResponse();
    $this->assertEquals(static::$authenticated_user->getUsername(), $response->getContent());
  }

  public function testHelperCurrentUserAnonymous() {
    $this->sendRequestAs('GET', '/test/get-current-username');
    $response = $this->client->getResponse();
    $this->assertSame('anon.', $response->getContent());
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    * @expectedExceptionMessageRegExp /service tag to register a permission type/
    */
  public function testCheckAccessPermissionTypeNotRegistered() {
    $this->la->checkAccess(['test' => 'hej'], ['user' => 'anon.']);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    * @expectedExceptionMessageRegExp /^An exception was caught while checking access: /
    */
  public function testCheckAccessOtherExceptions() {
    $this->la->checkAccess(['test' => 'hej'], []);
  }

  public function testCheckAccessDisallow() {
    $lpProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $lpProxy->addType($type);
    $lpProxy->setBypassCallback(function($context) { return false; });
    $la = new LogicalAuthorization($lpProxy, $this->helper);
    $this->assertFalse($la->checkAccess(['test' => 'no'], []));
  }

  public function testCheckAccessAllow() {
    $lpProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $lpProxy->addType($type);
    $lpProxy->setBypassCallback(function($context) { return false; });
    $la = new LogicalAuthorization($lpProxy, $this->helper);
    $this->assertTrue($la->checkAccess(['test' => 'yes'], []));
  }

  public function testGetAvailableActionsModelClass() {
    $model = new TestModelBoolean();
    $available_actions = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    foreach($available_actions as $key => $value) {
      if($key !== 'fields') {
        $this->assertSame($key, $value);
        continue;
      }
      foreach($value as $field_name => $field_actions) {
        $this->assertTrue(property_exists($model, $field_name));
        foreach($field_actions as $field_action_key => $field_action_value) {
          $this->assertSame($field_action_key, $field_action_value);
        }
      }
    }
  }

  public function testGetAvailableActionsModelObject() {
    $model = new TestModelBoolean();
    $available_actions_model = $this->laModel->getAvailableActions($model, ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    $available_actions_class = $this->laModel->getAvailableActions(get_class($model), ['create', 'read', 'update', 'delete'], ['get', 'set'], 'anon.');
    $this->assertSame($available_actions_model, $available_actions_class);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessWrongModelType() {
    $user = new TestUser();
    $this->laModel->checkModelAccess(null, 'read', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessModelClassDoesntExist() {
    $user = new TestUser();
    $this->laModel->checkModelAccess('TestModelBoolean', 'read', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessWrongActionType() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkModelAccess($model, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessEmptyAction() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkModelAccess($model, '', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckModelAccessWrongUserType() {
    $model = new TestModelBoolean();
    $this->laModel->checkModelAccess($model, 'read', []);
  }

  public function testCheckModelAccessMissingUser() {
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkModelAccess($model, 'read'));
  }

  public function testCheckModelAccessMissingPermissions() {
    $user = new TestUser();
    $model = new ErroneousModel();
    $this->assertTrue($this->laModel->checkModelAccess($model, 'read', $user));
  }

  public function testCheckModelAccessClassDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertFalse($this->laModel->checkModelAccess(get_class($model), 'read', $user));
  }

  public function testCheckModelAccessClassAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkModelAccess(get_class($model), 'create', $user));
  }

  public function testCheckModelAccessObjectDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertFalse($this->laModel->checkModelAccess($model, 'read', $user));
  }

  public function testCheckModelAccessObjectAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkModelAccess($model, 'create', $user));
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongModelType() {
    $user = new TestUser();
    $this->laModel->checkFieldAccess(null, null, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessModelClassDoesntExist() {
    $user = new TestUser();
    $this->laModel->checkFieldAccess('TestModelBoolean', null, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongFieldType() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, null, null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessEmptyField() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, '', null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongActionType() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, 'field1', null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessEmptyAction() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, 'field1', '', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckFieldAccessWrongUserType() {
    $model = new TestModelBoolean();
    $this->laModel->checkFieldAccess($model, 'field1', 'get', []);
  }

  public function testCheckFieldAccessMissingUser() {
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'set'));
  }

  public function testCheckFieldAccessMissingModelPermissions() {
    $user = new TestUser();
    $model = new ErroneousModel();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', $user));
  }

  public function testCheckFieldAccessMissingFieldPermissions() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'test', 'set', $user));
  }

  public function testCheckFieldAccessWrongAction() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'read', $user));
  }

  public function testCheckFieldAccessDisallow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertFalse($this->laModel->checkFieldAccess($model, 'field1', 'set', $user));
  }

  public function testCheckFieldAccessAllow() {
    $user = new TestUser();
    $model = new TestModelBoolean();
    $this->assertTrue($this->laModel->checkFieldAccess($model, 'field1', 'get', $user));
  }

  public function testGetAvailableRoutes() {
    $available_routes = $this->laRoute->getAvailableRoutes('anon.');
    $this->assertTrue(isset($available_routes['routes']) && is_array($available_routes['routes']) && !empty($available_routes['routes']));
    foreach($available_routes['routes'] as $key => $value) {
      $this->assertSame($key, $value);
    }
    $this->assertTrue(isset($available_routes['route_patterns']) && is_array($available_routes['route_patterns']) && !empty($available_routes['route_patterns']));
    foreach($available_routes['route_patterns'] as $key => $value) {
      $this->assertSame($key, $value);
    }
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessWrongRouteType() {
    $user = new TestUser();
    $this->laRoute->checkRouteAccess(null, $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessEmptyRoute() {
    $user = new TestUser();
    $this->laRoute->checkRouteAccess('', $user);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessWrongUserType() {
    $this->laRoute->checkRouteAccess('route_allowed', []);
  }

  /**
    * @expectedException Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException
    */
  public function testCheckRouteAccessRouteDoesntExist() {
    $user = new TestUser();
    $this->laRoute->checkRouteAccess('hej', $user);
  }

  public function testCheckRouteAccessMissingUser() {
    $this->assertTrue($this->laRoute->checkRouteAccess('route_no_bypass'));
  }

  public function testCheckRouteAccessDisallow() {
    $this->assertFalse($this->laRoute->checkRouteAccess('route_no_bypass', 'anon.'));
  }

  public function testCheckRouteAccessAllow() {
    $this->assertTrue($this->laRoute->checkRouteAccess('route_allowed', 'anon.'));
  }

  /**
    * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException
    */
  public function testLogicalPermissionsProxyAddTypeAlreadyExists() {
    $laProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $laProxy->addType($type);
    $laProxy->addType($type);
  }

  public function testLogicalPermissionsProxyAddType() {
    $laProxy = new LogicalPermissionsProxy();
    $type = new TestType();
    $laProxy->addType($type);
    $this->assertTrue($laProxy->typeExists('test'));
  }

  /**
    * @expectedException Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException
    */
  public function testLogicalPermissionsProxyCheckAccessTypeDoesntExist() {
    $laProxy = new LogicalPermissionsProxy();
    $laProxy->checkAccess(['test' => 'hej'], []);
  }

  public function testGetTree() {
    $tree = $this->treeBuilder->getTree();
    $this->assertTrue(!isset($tree['fetch']));
    $tree = $this->treeBuilder->getTree(false, true);
    $this->assertSame('static_cache', $tree['fetch']);
    $tree = $this->treeBuilder->getTree(true, true);
    $this->assertSame('no_cache', $tree['fetch']);
  }

  public function testGetTreeFromCache() {
    $tree = $this->treeBuilder->getTree(false, true);
    $this->assertSame('cache', $tree['fetch']);
  }

  /**
    * @expectedException InvalidArgumentException
    */
  public function testEventInsertTreeWrongTreeType() {
    $laProxy = new LogicalPermissionsProxy();
    $event = new AddPermissionsEvent($laProxy->getValidPermissionKeys());
    $event->insertTree('key');
  }

  public function testEventInsertTreeGetTree() {
    $laProxy = new LogicalPermissionsProxy();
    $role = new Role();
    $laProxy->addType($role);
    $flagManager = new FlagManager();
    $laProxy->addType($flagManager);
    $event = new AddPermissionsEvent($laProxy->getValidPermissionKeys());
    $tree1 = [
      'models' => [
        'testmodel' => [
          'create' => [
            'role' => 'role1',
          ],
          'read' => [
            'flag' => [
              'flag1',
              'flag2',
            ],
          ],
          'update' => [
            'flag' => 'flag1',
          ],
          'fields' => [
            'field1' => [
              'get' => [
                'role' => 'role1',
              ],
              'set' => [
                'flag' => 'flag1',
              ],
            ],
          ],
        ],
      ],
    ];
    $tree2 = [
      'models' => [
        'testmodel' => [
          'create' => [
            'role' => [
              'newrole1',
              'newrole2',
            ],
          ],
          'read' => [
            'flag' => 'newflag1',
          ],
          'fields' => [
            'field1' => [
              'get' => [
                'OR' => [
                  'role' => 'newrole1',
                  'flag' => 'newflag1',
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $result = [
      'models' => [
        'testmodel' => [
          'create' => [
            'role' => [
              'newrole1',
              'newrole2',
            ],
          ],
          'read' => [
            'flag' => 'newflag1',
          ],
          'update' => [
            'flag' => 'flag1',
          ],
          'fields' => [
            'field1' => [
              'get' => [
                'OR' => [
                  'role' => 'newrole1',
                  'flag' => 'newflag1',
                ],
              ],
              'set' => [
                'flag' => 'flag1',
              ],
            ],
          ],
        ],
      ],
    ];

    $event->insertTree($tree1);
    $event->insertTree($tree2);
    $this->assertSame($result, $event->getTree());
  }
}