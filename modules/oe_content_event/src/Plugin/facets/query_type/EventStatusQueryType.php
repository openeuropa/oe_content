<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Plugin\facets\query_type;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\facets\QueryType\QueryTypePluginBase;
use Drupal\facets\Result\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides support for event status facets.
 *
 * @FacetsQueryType(
 *   id = "oe_content_event_query_type",
 *   label = @Translation("Event status"),
 * )
 */
class EventStatusQueryType extends QueryTypePluginBase implements ContainerFactoryPluginInterface {

  /**
   * Option for upcoming events.
   */
  const UPCOMING = 'coming';

  /**
   * Option for past events.
   */
  const PAST = 'past';

  /**
   * The system time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs the EventStatusQueryType plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The system time.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $query = $this->query;
    // Only alter the query when there's an actual query object to alter.
    if (empty($query)) {
      return;
    }

    $field_identifier = $this->facet->getFieldIdentifier();
    // Add the filter to the query if there are active values.
    $active_items = $this->facet->getActiveItems();
    $now = $this->getCurrentTime();
    if (count($active_items)) {
      $filter = $query->createConditionGroup('OR', ['facet:' . $field_identifier]);
      foreach ($active_items as $value) {
        if ($value == self::PAST) {
          $filter->addCondition($this->facet->getFieldIdentifier(), $now->getTimestamp(), "<=");
          $query->sort($this->facet->getFieldIdentifier(), 'DESC');
        }
        elseif ($value = self::UPCOMING) {
          $filter->addCondition($this->facet->getFieldIdentifier(), $now->getTimestamp(), ">");
          $query->sort($this->facet->getFieldIdentifier(), 'ASC');
        }
      }
      $query->addConditionGroup($filter);
    }
  }

  /**
   * Gets current date time.
   *
   * @return \DateTimeZone
   *   The date time.
   */
  protected function getCurrentTime(): DrupalDateTime {
    $now = new DrupalDateTime();
    $current_time = $this->time->getCurrentTime();
    $now->setTimestamp($current_time);
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    return $now;
  }

  /**
   * Provides default options.
   *
   * @return array
   *   The default options.
   */
  protected function defaultOptions(): array {
    return [
      EventStatusQueryType::PAST => t('Past events'),
      EventStatusQueryType::UPCOMING => t('Upcoming events'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $now = $this->getCurrentTime();
    $count = [];
    $count[self::UPCOMING] = $count[self::PAST] = 0;
    if (!empty($this->results)) {
      $facet_results = [];
      foreach ($this->results as $result) {
        $result_filter = $result['filter'];
        $now->getTimestamp() > $result_filter ? $count[self::UPCOMING]++ : $count[self::PAST]++;
      }
    }

    // Get default options for event status.
    $default_options = $this->defaultOptions();
    foreach ($default_options as $raw => $display) {
      $item_count = $count[$raw] ?? 0;
      $result = new Result($this->facet, $raw, $display, $item_count);
      $facet_results[] = $result;
    }

    $this->facet->setResults($facet_results);
    return $this->facet;
  }

}
