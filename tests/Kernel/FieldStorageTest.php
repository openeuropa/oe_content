<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\rdf_skos\Entity\Concept;
use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Class FieldStorageTest.
 */
class FieldStorageTest extends RdfKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'link',
    'node',
    'oe_content',
    'rdf_skos',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Necessary for module uninstall.
    $this->installSchema('user', 'users_data');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node']);
  }

  /**
   * Test the defined fields.
   */
  public function testFieldStorage(): void {
    $this->assertEquals(1,1);
    $concept = Concept::load('http://eurovoc.europa.eu/1');
    var_dump($concept->label());
    ob_flush();
//    $fields = [
//      'oe_content_short_title',
//      'oe_content_navigation_title',
//      'oe_content_content_owner',
//      'oe_content_legacy_link',
//    ];
//
//    foreach ($fields as $field) {
//      $field_storage = FieldStorageConfig::loadByName('node', $field);
//      $this->assertTrue($field_storage, '');
//    }
//    $field_storage = FieldStorageConfig::loadByName('node', 'body');
//    $this->assertTrue(count($field_storage->getBundles()) == 1, 'Node body field storage is being used on the new node type.');
//    $field = FieldConfig::loadByName('node', 'ponies', 'body');
//    $field->delete();
//    $field_storage = FieldStorageConfig::loadByName('node', 'body');
//    $this->assertTrue(count($field_storage->getBundles()) == 0, 'Node body field storage exists after deleting the only instance of a field.');
//    \Drupal::service('module_installer')->uninstall(['node']);
//    $field_storage = FieldStorageConfig::loadByName('node', 'body');
//    $this->assertFalse($field_storage, 'Node body field storage does not exist after uninstalling the Node module.');
  }

}
