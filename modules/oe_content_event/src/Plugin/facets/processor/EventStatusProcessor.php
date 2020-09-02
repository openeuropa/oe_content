<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\PreQueryProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\oe_content_event\Plugin\facets\query_type\EventStatusQueryType;

/**
 * Provides a processor to handle event status.
 *
 * @FacetsProcessor(
 *   id = "oe_content_event_status",
 *   label = @Translation("OE Event status"),
 *   description = @Translation("Assign correct query type for event status"),
 *   stages = {
 *     "pre_query" =60,
 *   }
 * )
 */
class EventStatusProcessor extends ProcessorPluginBase implements PreQueryProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function preQuery(FacetInterface $facet) {
    $active_items = $facet->getActiveItems();
    if (empty($active_items)) {
      $facet->setActiveItems([EventStatusQueryType::UPCOMING]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    return 'oe_content_event_status_comparison';
  }

}
