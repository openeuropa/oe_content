<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Context\Annotation;

use Behat\Behat\Context\Annotation\AnnotationReader;
use Drupal\Tests\oe_content\Behat\Hook\Call\AfterParseEntityFields;
use Drupal\Tests\oe_content\Behat\Hook\Call\AfterSaveEntity;
use Drupal\Tests\oe_content\Behat\Hook\Call\BeforeParseEntityFields;
use Drupal\Tests\oe_content\Behat\Hook\Call\BeforeSaveEntity;

/**
 * Entity aware annotation readers.
 *
 * Read annotation that are Drupal content entity-aware, this means annotations
 * that provides an entity type and its bundle, as match 2 and 3 of their
 * matching regular expression.
 */
class EntityAwareAnnotationReader implements AnnotationReader {

  /**
   * List of matching regular expressions and related hook caller class.
   *
   * @var array
   */
  protected $matchers = [
    // Matches @BeforeParseEntityFields(node,article).
    '/^\@(BeforeParseEntityFields\((\w+), ?(\w+)\))(?:\s+(.+))?$/i' => BeforeParseEntityFields::class,
    // Matches @AfterParseEntityFields(node,article).
    '/^\@(AfterParseEntityFields\((\w+), ?(\w+)\))(?:\s+(.+))?$/i' => AfterParseEntityFields::class,
    // Matches @BeforeSaveEntity(node,article).
    '/^\@(BeforeSaveEntity\((\w+), ?(\w+)\))(?:\s+(.+))?$/i' => BeforeSaveEntity::class,
    // Matches @AfterSaveEntity(node,article).
    '/^\@(AfterSaveEntity\((\w+), ?(\w+)\))(?:\s+(.+))?$/i' => AfterSaveEntity::class,
  ];

  /**
   * {@inheritdoc}
   */
  public function readCallee($contextClass, \ReflectionMethod $method, $docLine, $description) {

    foreach ($this->matchers as $regex => $class) {
      if (!preg_match($regex, $docLine, $match)) {
        continue;
      }
      $entity_type = $match[2];
      $bundle = $match[3];
      $callable = [$contextClass, $method->getName()];
      return new $class($entity_type, $bundle, $callable, $description);
    }
  }

}
