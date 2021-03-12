<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_person\Kernel;

use Drupal\oe_content_person\PersonNodeWrapper;

/**
 * Tests the PersonNodeWrapper.
 *
 * @coversDefaultClass \Drupal\oe_content_person\PersonNodeWrapper
 */
class PersonNodeWrapperTest extends PersonEntityTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('oe_person_job');
  }

  /**
   * Tests the getPersonJobLabels method of the PersonNodeWrapper class.
   *
   * @covers ::getPersonJobLabels
   */
  public function testGetPersonJobLabels(): void {
    $default_label_job = $this->entityTypeManager->getStorage('oe_person_job')->create([
      'type' => 'oe_default',
    ]);
    $default_label_job->save();

    $name_role_job = $this->entityTypeManager->getStorage('oe_person_job')->create([
      'type' => 'oe_default',
      'oe_role_name' => 'Role name label',
    ]);
    $name_role_job->save();

    $referenced_role_job = $this->entityTypeManager->getStorage('oe_person_job')->create([
      'type' => 'oe_default',
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/corporate-body/APEC',
    ]);
    $referenced_role_job->save();

    $acting_role_job = $this->entityTypeManager->getStorage('oe_person_job')->create([
      'type' => 'oe_default',
      'oe_role_reference' => 'http://publications.europa.eu/resource/authority/corporate-body/APEC',
      'oe_acting' => TRUE,
    ]);
    $acting_role_job->save();

    $person = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'oe_person',
      'oe_person_type' => 'eu',
      'oe_person_first_name' => 'John',
      'oe_person_last_name' => 'Doe',
      'oe_person_jobs' => [
        [
          'target_id' => $default_label_job->id(),
          'target_revision_id' => $default_label_job->getRevisionId(),
        ],
        [
          'target_id' => $name_role_job->id(),
          'target_revision_id' => $name_role_job->getRevisionId(),
        ],
        [
          'target_id' => $referenced_role_job->id(),
          'target_revision_id' => $referenced_role_job->getRevisionId(),
        ],
        [
          'target_id' => $acting_role_job->id(),
          'target_revision_id' => $acting_role_job->getRevisionId(),
        ],
      ],
    ]);
    $person->save();

    $wrapper = PersonNodeWrapper::getInstance($person);
    $expected_labels = [
      'Default',
      'Role name label',
      'Asia-Pacific Economic Cooperation',
      '(Acting) Asia-Pacific Economic Cooperation',
    ];
    $this->assertEquals($expected_labels, $wrapper->getPersonJobLabels());
  }

}
