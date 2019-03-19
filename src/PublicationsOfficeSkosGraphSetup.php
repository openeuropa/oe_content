<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service used to set up the SKOS graphs of the Publications Office.
 */
class PublicationsOfficeSkosGraphSetup {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * PublicationsOfficeSkosGraphSetup constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Gets the graph information for the OP vocabularies.
   *
   * @todo instead of hardcoding, use the content layer to determine these.
   */
  protected function getGraphInfo(): array {
    return [
      'corporate_body' => 'http://publications.europa.eu/resource/authority/corporate-body',
      'corporate_body_classification' => 'http://publications.europa.eu/resource/authority/corporate-body-classification',
      'target_audience' => 'http://publications.europa.eu/resource/authority/target-audience',
      'organisation_type' => 'http://publications.europa.eu/resource/authority/organization-type',
      'resource_type' => 'http://publications.europa.eu/resource/authority/resource-type',
      'place' => 'http://publications.europa.eu/resource/dataset/place',
      'public_event_type' => 'http://publications.europa.eu/resource/dataset/public-event-type',
      'eurovoc' => 'http://publications.europa.eu/resource/dataset/eurovoc',
    ];
  }

  /**
   * Sets up the graphs.
   */
  public function setup(): void {
    $graphs = $this->getGraphInfo();
    $config = [];
    foreach ($graphs as $name => $graph) {
      $config['skos_concept_scheme'][] = [
        'name' => $name,
        'uri' => $graph,
      ];

      $config['skos_concept'][] = [
        'name' => $name,
        'uri' => $graph,
      ];
    }

    $this->configFactory->getEditable('rdf_skos.graphs')->set('entity_types', $config)->save();
  }

}
