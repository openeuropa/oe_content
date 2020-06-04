<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Plugin\InternalLinkSourceFilter;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterInterface;
use Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterPluginBase;
use Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event period link source filter class.
 *
 * @InternalLinkSourceFilter(
 *   id = "oe_content_event_period",
 *   label = @Translation("Event period link source filter"),
 *   description = @Translation("Filters events by the period they are in: past or upcoming."),
 *   entity_types = {
 *     "node" = {
 *       "oe_event",
 *     },
 *   },
 * )
 */
class EventPeriodFilter extends InternalLinkSourceFilterPluginBase implements InternalLinkSourceFilterInterface, ContainerFactoryPluginInterface {

  /**
   * Option for upcoming events.
   */
  const UPCOMING = 1;

  /**
   * Option for past events.
   */
  const PAST = -1;

  /**
   * The time based cache generator.
   *
   * @var \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface
   */
  protected $timeBasedCacheTagGenerator;

  /**
   * The system time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'period' => self::UPCOMING,
    ];
  }

  /**
   * Constructs the EventPeriodFilter plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\oe_time_caching\Cache\TimeBasedCacheTagGeneratorInterface $time_based_cache_tag_generator
   *   The time based cache generator.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The system time.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeBasedCacheTagGeneratorInterface $time_based_cache_tag_generator, TimeInterface $time, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->timeBasedCacheTagGenerator = $time_based_cache_tag_generator;
    $this->time = $time;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('oe_time_caching.time_based_cache_tag_generator'),
      $container->get('datetime.time'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function apply(QueryInterface $query, array $context, RefinableCacheableDependencyInterface $cacheability): void {
    $now = new DrupalDateTime();
    $current_time = $this->time->getCurrentTime();
    $now->setTimestamp($current_time);
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    switch ($this->getConfiguration()['period']) {
      case self::PAST:
        $query->condition('oe_event_dates.end_value', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), "<");
        $query->sort('oe_event_dates.end_value', 'DESC');
        // Time based cache tags need to be invalidated when a new event
        // ends so find out which event is going to end next and extract
        // its end date.
        $this->addTimeCacheTags($cacheability, 'end_value');
        break;

      case self::UPCOMING:
        $query->condition('oe_event_dates.value', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), ">");
        $query->sort('oe_event_dates.value', 'ASC');
        // Time based cache tags need to be invalidated when the next
        // upcoming event starts so execute the query and extract the
        // starting date of the first event in the list.
        $this->addTimeCacheTags($cacheability);
        break;
    }
  }

  /**
   * Helper function to add time based cache tags to the query cacheability.
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cache
   *   The refinable cacheability metadata for the current plugin.
   * @param string $date_field_value_id
   *   The id of the value to use on the datefield. It defaults to 'value'.
   */
  protected function addTimeCacheTags(RefinableCacheableDependencyInterface &$cache, string $date_field_value_id = 'value') {
    $now = new DrupalDateTime();
    $current_time = $this->time->getCurrentTime();
    $now->setTimestamp($current_time);
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $results = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('oe_event_dates.' . $date_field_value_id, $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), ">")
      ->sort('oe_event_dates.' . $date_field_value_id, 'ASC')
      ->execute();
    if (!empty($results)) {
      $nex_event = $this->entityTypeManager->getStorage('node')->load(reset($results));
      $next_event_datetime = new DrupalDateTime($nex_event->oe_event_dates->{$date_field_value_id}, new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $cache->addCacheTags($this->timeBasedCacheTagGenerator->generateTags($next_event_datetime->getPhpDateTime()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['period'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose whether to show past or upcoming events.'),
      '#default_value' => $this->getConfiguration()['period'] ?? self::UPCOMING,
      '#empty_value' => 'all',
      '#empty_option' => $this->t('Show all'),
      '#options' => [
        self::PAST => $this->t('Past events'),
        self::UPCOMING => $this->t('Upcoming events'),
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['period'] = $form_state->getValue('period');
  }

}
