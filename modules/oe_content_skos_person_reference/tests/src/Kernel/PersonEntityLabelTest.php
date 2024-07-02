<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_skos_person_reference\Kernel;

use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Tests Political leader Person entity bundle.
 */
class PersonEntityLabelTest extends SparqlKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'entity_reference_revisions',
    'user',
    'rdf_skos',
    'system',
    'oe_content_sub_entity',
    'oe_content_sub_entity_person',
    'oe_content_skos_person_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('oe_person');
    $this->installEntitySchema('node');
    $this->installConfig([
      'node',
      'oe_content_sub_entity_person',
    ]);
    \Drupal::moduleHandler()->loadInclude('oe_content_skos_person_reference', 'install');
    oe_content_skos_person_reference_install(FALSE);
  }

  /**
   * Tests label of Political leader type Person entities.
   */
  public function testLabel(): void {
    $person = $this->container->get('entity_type.manager')->getStorage('oe_person')->create([
      'type' => 'oe_political_leader',
    ]);
    foreach ($this->getTestData() as $data) {
      $field_values = NULL;
      if (!empty($data['political_leaders'])) {
        $field_values = $data['political_leaders'];
      }
      $person->set($data['reference_field_name'], $field_values);
      $person->save();
      $this->assertEquals($data['expected_label'], $person->label());
    }
  }

  /**
   * Testing data.
   *
   * @return array
   *   Array of test cases.
   */
  protected function getTestData(): array {
    return [
      [
        'reference_field_name' => 'oe_skos_reference',
        'corporate_bodies' => NULL,
        'expected_label' => 'EU Political leader',
      ],
      [
        'reference_field_name' => 'oe_skos_reference',
        'political_leaders' => [
          'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0440FF',
        ],
        'expected_label' => 'Ursula von der Leyen',
      ],
      [
        'reference_field_name' => 'oe_skos_reference',
        'political_leaders' => [
          'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0440FF',
          'http://publications.europa.eu/resource/authority/political-leader/COM_00006A044747',
        ],
        'expected_label' => 'Ursula von der Leyen, Nicolas Schmit',
      ],
    ];
  }

}
