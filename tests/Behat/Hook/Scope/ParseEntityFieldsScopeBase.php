<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

use Behat\Testwork\Environment\Environment;
use Drupal\Tests\oe_content\Behat\Hook\Traits\EntityAwareTrait;

/**
 * Base scope class for before and after RawDrupalContext::parseEntityFields().
 */
abstract class ParseEntityFieldsScopeBase implements EntityAwareHookScopeInterface {

  use EntityAwareTrait;

  /**
   * Behat environment.
   *
   * @var \Behat\Testwork\Environment\Environment
   */
  protected $environment;

  /**
   * Fields to be altered.
   *
   * @var array
   */
  protected $fields;

  /**
   * ParseEntityFieldsScopeBase constructor.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param \Behat\Testwork\Environment\Environment $environment
   *   Behat environment.
   * @param array $fields
   *   Fields to be altered.
   */
  public function __construct(string $entity_type, string $bundle, Environment $environment, array $fields) {
    $this->entityType = $entity_type;
    $this->bundle = $bundle;
    $this->environment = $environment;
    $this->fields = $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironment() {
    return $this->environment;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuite() {
    return $this->environment->getSuite();
  }

  /**
   * Get fields.
   *
   * @return array
   *   Fields to be altered.
   */
  public function getFields(): array {
    return $this->fields;
  }

  /**
   * Set fields.
   *
   * @param array $fields
   *   Fields array.
   */
  public function setFields(array $fields): void {
    $this->fields = $fields;
  }

}
