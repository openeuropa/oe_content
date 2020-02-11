<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

use Behat\Testwork\Environment\Environment;
use Drupal\Tests\oe_content\Behat\Hook\Traits\EntityAwareTrait;

/**
 * Scope class for CorporateFieldsAlter tags.
 */
class CorporateFieldsAlterScope implements EntityAwareHookScopeInterface {

  use EntityAwareTrait;

  /**
   * Scope name.
   */
  const NAME = 'corporate.fields.alter';

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
   * CorporateFieldsAlterScope constructor.
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
  public function getName() {
    return self::NAME;
  }

  /**
   * {@inheritDoc}
   */
  public function getEnvironment() {
    return $this->environment;
  }

  /**
   * {@inheritDoc}
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

}
