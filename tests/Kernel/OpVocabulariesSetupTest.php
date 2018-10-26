<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\rdf_skos\Entity\ConceptInterface;
use Drupal\rdf_skos\Entity\ConceptSchemeInterface;
use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests that the OP vocabularies get configured and can be loaded.
 */
class OpVocabulariesSetupTest extends RdfKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'rdf_skos',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['oe_content', 'rdf_skos']);
    $this->installEntitySchema('skos_concept_scheme');
    $this->installEntitySchema('skos_concept');

    $this->container->get('module_handler')->loadInclude('oe_content', 'install');
    oe_content_install();
  }

  /**
   * Tests that we can load Concepts and Schemes from the OP vocabularies.
   */
  public function testOpVocabularies() {
    $concept_scheme = $this->container->get('entity_type.manager')->getStorage('skos_concept_scheme')->load('http://eurovoc.europa.eu/100141');
    $this->assertInstanceOf(ConceptSchemeInterface::class, $concept_scheme);
    $concept = $this->container->get('entity_type.manager')->getStorage('skos_concept')->load('http://eurovoc.europa.eu/1');
    $this->assertInstanceOf(ConceptInterface::class, $concept);
  }

}
