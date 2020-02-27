<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Test corporate entity routes access.
 */
class CorporateEntityRouteAccessTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content_entity',
    'oe_content_entity_test',
    'system',
    'user',
  ];

  /**
   * Test access for collection route.
   */
  public function testCollectionRouteAccess() {
    $access_manager = $this->container->get('access_manager');

    // Administrator.
    $user = $this->drupalCreateUser(['manage corporate content entities']);
    $actual = $access_manager->checkNamedRoute('entity.oe_corporate_entity_test.collection', [], $user, TRUE);
    $this->assertTrue($actual->isAllowed());

    // User with access overview permission.
    $user = $this->drupalCreateUser(['access oe_corporate_entity_test overview']);
    $actual = $access_manager->checkNamedRoute('entity.oe_corporate_entity_test.collection', [], $user, TRUE);
    $this->assertTrue($actual->isAllowed());

    // User without permissions.
    $user = $this->drupalCreateUser([]);
    $actual = $access_manager->checkNamedRoute('entity.oe_corporate_entity_test.collection', [], $user, TRUE);
    $this->assertTrue($actual->isNeutral());
  }

}
