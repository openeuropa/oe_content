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
 *   id = "event_link_source_filter",
 *   label = @Translation("Event Link Source Filter"),
 *   description = @Translation("Filters for link lists using events as the internal source."),
 *   entity_types = {
 *     "node" = {
 *       "oe_event",
 *     },
 *   },
 * )
 */
class EventLinkSourceFilter extends InternalLinkSourceFilterPluginBase implements InternalLinkSourceFilterInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'time' => 'upcoming',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(string $entity_type, string $bundle): bool {
    $applicable_entity_types = $this->pluginDefinition['entity_types'];
    if (isset($applicable_entity_types[$entity_type])) {
      $applicable_bundles = $applicable_entity_types[$entity_type];
      if (in_array($bundle, $applicable_bundles)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function apply(QueryInterface $query, array $context, RefinableCacheableDependencyInterface $cacheability): void {
    $now = new DrupalDateTime('now');
    $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    switch ($this->getConfiguration()['time']) {
      case 'past':
        $query->condition('oe_event_dates.end_value', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), "<=");
        $query->sort('oe_event_dates.end_value', 'DESC');
        break;

      case 'upcoming':
        $query->condition('oe_event_dates.value', $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), ">=");
        $query->sort('oe_event_dates.value', 'ASC');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['time'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose whether to show past or upcoming events.'),
      '#default_value' => $this->getConfiguration()['time'] ?? 'upcoming',
      '#empty_value' => 'all',
      '#empty_option' => $this->t('Show all'),
      '#options' => [
        'past' => $this->t('Past events'),
        'upcoming' => $this->t('Upcoming events'),
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['time'] = $form_state->getValue('time');
  }

}
