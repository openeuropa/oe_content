<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Plugin\InternalLinkSourceFilter;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Datetime\DrupalDateTime;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeBasedCacheTagGeneratorInterface $time_based_cache_tag_generator, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->timeBasedCacheTagGenerator = $time_based_cache_tag_generator;
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
      $container->get('oe_time_caching.time_based_cache_tag_generator'),
      $container->get('datetime.time')
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
    $cacheability->addCacheTags($this->timeBasedCacheTagGenerator->generateTags($now->getPhpDateTime()));
    switch ($this->getConfiguration()['period']) {
      case self::PAST:
        $query->condition('oe_event_dates.end_value', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), "<");
        $query->sort('oe_event_dates.end_value', 'DESC');
        break;

      case self::UPCOMING:
        $query->condition('oe_event_dates.value', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), ">");
        $query->sort('oe_event_dates.value', 'ASC');
        break;
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
