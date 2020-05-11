<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Plugin\InternalLinkSourceFilter;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterInterface;
use Drupal\oe_link_lists_internal_source\InternalLinkSourceFilterPluginBase;

/**
 * Event link source filter class.
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
class EventPeriodFilter extends InternalLinkSourceFilterPluginBase implements InternalLinkSourceFilterInterface {

  /**
   * Option for upcoming events.
   */
  const UPCOMING = 1;

  /**
   * Option for past events.
   */
  const PAST = -1;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'period' => self::UPCOMING,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(string $entity_type, string $bundle): bool {
    if (isset($this->pluginDefinition['entity_types'][$entity_type]) && in_array($bundle, $this->pluginDefinition['entity_types'][$entity_type])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function apply(QueryInterface $query, array $context, RefinableCacheableDependencyInterface $cacheability): void {
    $now = new DrupalDateTime('now');
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
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
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['period'] = $form_state->getValue('period');
  }

}
