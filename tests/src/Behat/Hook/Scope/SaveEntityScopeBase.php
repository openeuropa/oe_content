<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

use Behat\Testwork\Environment\Environment;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Tests\oe_content\Behat\Hook\Traits\EntityAwareTrait;

/**
 * Base scope class for before and after saving an entity.
 */
abstract class SaveEntityScopeBase implements EntityAwareHookScopeInterface {

  use EntityAwareTrait;

  /**
   * Behat environment.
   *
   * @var \Behat\Testwork\Environment\Environment
   */
  protected $environment;

  /**
   * Entity object.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * ParseEntityFieldsScopeBase constructor.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param \Behat\Testwork\Environment\Environment $environment
   *   Behat environment.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity object.
   */
  public function __construct(string $entity_type, string $bundle, Environment $environment, ContentEntityInterface $entity) {
    $this->entityType = $entity_type;
    $this->bundle = $bundle;
    $this->environment = $environment;
    $this->entity = $entity;
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
   * Get entity object.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   Entity object.
   */
  public function getEntity(): ContentEntityInterface {
    return $this->entity;
  }

}
