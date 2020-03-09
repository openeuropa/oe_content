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
   * Add given fields.
   *
   * @param array $fields
   *   List of fields to be added.
   *
   * @return \Drupal\Tests\oe_content\Behat\Hook\Scope\ParseEntityFieldsScopeBase
   *   Scope object.
   */
  public function addFields(array $fields): self {
    $this->fields += $fields;
    return $this;
  }

  /**
   * Rename field.
   *
   * @param string $current_name
   *   Current field name.
   * @param string $new_name
   *   New field name.
   *
   * @return \Drupal\Tests\oe_content\Behat\Hook\Scope\ParseEntityFieldsScopeBase
   *   Scope object.
   */
  public function renameField(string $current_name, string $new_name): self {
    $this->fields[$new_name] = $this->fields[$current_name];
    $this->removeField($current_name);
    return $this;
  }

  /**
   * Remove field.
   *
   * @param string $name
   *   Field name.
   *
   * @return \Drupal\Tests\oe_content\Behat\Hook\Scope\ParseEntityFieldsScopeBase
   *   Scope object.
   */
  public function removeField(string $name): self {
    unset($this->fields[$name]);
    return $this;
  }

}
