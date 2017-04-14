<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;
use Ordermind\LogicalAuthorizationBundle\Doctrine\Annotation\LogicalAuthorization;

class AddDoctrinePermissions implements EventSubscriberInterface {
  protected $doctrine;
  protected $annotationDriverClass;
  protected $xmlDriverClass;
  protected $ymlDriverClass;

  public function __construct(ManagerRegistry $doctrine = null, $annotationDriverClass = null, $xmlDriverClass = null, $ymlDriverClass = null) {
    $this->doctrine = $doctrine;
    $this->annotationDriverClass = $annotationDriverClass;
    $this->xmlDriverClass = $xmlDriverClass;
    $this->ymlDriverClass = $ymlDriverClass;
  }

  public static function getSubscribedEvents() {
    return array(
      'logical_authorization.add_permissions' => array(
        array(
          'addPermissions',
        ),
      ),
    );
  }

  public function addPermissions(AddPermissionsEvent $event) {
    if(is_null($this->doctrine)) return;

    $object_managers = $this->doctrine->getManagers();
    foreach($object_managers as $om) {
      $metadataDriverImplementation = $om->getConfiguration()->getMetadataDriverImpl();
      $drivers = $metadataDriverImplementation->getDrivers();
      foreach($drivers as $driver) {
        $driver_class = get_class($driver);
        if($driver_class === $this->annotationDriverClass) {
          $this->addAnnotationPermissions($event, $driver, $om);
        }
        elseif($driver_class === $this->xmlDriverClass) {
          $this->addXMLPermissions($event, $driver);
        }
        elseif($driver_class === $this->ymlDriverClass) {
          $this->addYMLPermissions($event, $driver);
        }
      }
    }
  }

  protected function addAnnotationPermissions(AddPermissionsEvent $event, MappingDriver $driver, ObjectManager $om) {
    $classes = $driver->getAllClassNames();
    $annotationReader = $driver->getReader();
    $permissions = [];
    foreach($classes as $class) {
      $metadata = $om->getClassMetadata($class);
      $reflectionClass = new \ReflectionClass($class);
      $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
      foreach ($classAnnotations as $annotation) {
        if ($annotation instanceof LogicalAuthorization) {
          if(!isset($permissions['models'])) $permissions['models'] = [];
          $permissions['models'][$class] = $annotation->getPermissions();
        }
      }
      foreach($reflectionClass->getProperties() as $property) {
        $field_name = $property->getName();
        $propertyAnnotations = $annotationReader->getPropertyAnnotations($property);
        foreach ($propertyAnnotations as $annotation) {
          if ($annotation instanceof LogicalAuthorization) {
            if(!isset($permissions['models'])) $permissions['models'] = [];
            $permissions['models'] += [$class => ['fields' => []]];
            $permissions['models'][$class]['fields'][$field_name] = $annotation->getPermissions();
          }
        }
      }
    }
    $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
  }

  protected function addXMLPermissions(AddPermissionsEvent $event, MappingDriver $driver) {
    $classes = $driver->getAllClassNames();
    $permissions = [];
    foreach($classes as $class) {
      $xmlRoot = $driver->getElement($class);
      // Parse XML structure in $element
      if(isset($xmlRoot->{'logical-authorization'})) {
        if(!isset($permissions['models'])) $permissions['models'] = [];
        $permissions['models'][$class] = json_decode(json_encode($xmlRoot->{'logical-authorization'}), TRUE);
      }
      foreach(['id', 'field'] as $field_key) {
        if(isset($xmlRoot->{$field_key})) {
          foreach($xmlRoot->{$field_key} as $field) {
            $field_name = (string) $field['name'];
            if(isset($field->{'logical-authorization'})) {
              if(!isset($permissions['models'])) $permissions['models'] = [];
              $permissions['models'] += [$class => ['fields' => []]];
              $permissions['models'][$class]['fields'][$field_name] = json_decode(json_encode($field->{'logical-authorization'}), TRUE);
            }
          }
        }
      }
    }
    $permissions = $this->massagePermissionsRecursive($permissions);
    $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
  }

  protected function addYMLPermissions(AddPermissionsEvent $event, MappingDriver $driver) {
    $classes = $driver->getAllClassNames();
    $permissions = [];
    foreach($classes as $class) {
      $mapping = $driver->getElement($class);
      if(isset($mapping['logicalAuthorization'])) {
        if(!isset($permissions['models'])) $permissions['models'] = [];
        $permissions['models'][$class] = $mapping['logicalAuthorization'];
      }
      foreach(['id', 'field'] as $field_key) {
        if(isset($mapping[$field_key])) {
          foreach($mapping[$field_key] as $field_name => $field_mapping) {
            if(isset($field_mapping['logicalAuthorization'])) {
              if(!isset($permissions['models'])) $permissions['models'] = [];
              $permissions['models'] += [$class => ['fields' => []]];
              $permissions['models'][$class]['fields'][$field_name] = $field_mapping['logicalAuthorization'];
            }
          }
        }
      }
    }
    $permissions = $this->massagePermissionsRecursive($permissions);
    $event->setTree($event->mergePermissions([$event->getTree(), $permissions]));
  }

  protected function massagePermissionsRecursive($permissions) {
    $massaged_permissions = [];
    foreach($permissions as $key => $value) {
      if(is_array($value)) {
        $parsed_value = $this->massagePermissionsRecursive($value);
      }
      elseif(is_string($value)) {
        $lowercase_value = strtolower($value);
        if($lowercase_value === 'true') {
          $parsed_value = TRUE;
        }
        elseif($lowercase_value === 'false') {
          $parsed_value = FALSE;
        }
        else {
          $parsed_value = $value;
        }
      }
      else {
        $parsed_value = $value;
      }

      if($key === 'value') {
        $massaged_permissions[] = $parsed_value;
      }
      else {
        $massaged_permissions[$key] = $parsed_value;
      }
    }

    return $massaged_permissions;
  }
}
