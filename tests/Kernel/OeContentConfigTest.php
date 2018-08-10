<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the OE Content config.
 */
class OeContentConfigTest extends RdfKernelTestBase {

  /**
   * Test that the Provenance URI is set on the RDF entities..
   */
  public function testProvenanceUriConfig(): void {
    // Set the global provenance URI.
    \Drupal::configFactory()
      ->getEditable('oe_content.settings')
      ->set('provenance_uri', 'http://example.com')
      ->save();

    // Update the mapping of the Dummy RDF entity type for the provenance URI.
    $config = \Drupal::configFactory()
      ->getEditable('rdf_entity.mapping.rdf_entity.dummy.yml');
    $mappings = $config->get('base_fields_mapping');
    $mappings['provenance_uri'] = [
      'value' => [
        'predicate' => 'http://europa.eu/provenance_uri',
        'format' => 'xsd:anyURI'
      ]
    ];
    $config->set('base_fields_mapping', $mappings);
    $config->save();

    $rdf = \Drupal::entityTypeManager()->getStorage('rdf_entity')->create([
      'type' => 'dummy',
      'label' => 'My RDF entity',
      'field_text' => 'My text',
    ]);
    $rdf->save();

    var_dump($rdf->get('provenance_uri')->value);
  }

}
