<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service used to set up the SKOS graphs of the Publications Office.
 *
 * @deprecated in oe_content 1.8.2 and will be removed in 2.0.0. Use
 *   rdf_skos.op_skos_setup instead.
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
      'europa_digital_thesaurus' => 'http://data.europa.eu/uxp',
      'country' => 'http://publications.europa.eu/resource/authority/country',
      'language' => 'http://publications.europa.eu/resource/authority/language',
      'eu-programme' => 'http://publications.europa.eu/resource/authority/eu-programme',
      'sdg' => 'http://publications.europa.eu/resource/dataset/sdg',
    ];
  }

  /**
   * Sets up the graphs.
   */
  public function setup(): void {
    $graphs = $this->getGraphInfo();

    // Use the new Skos Graph Service.
    \Drupal::service('rdf_skos.skos_graph_configurator')->addGraphs($graphs);
  }

}
