<?php

declare(strict_types=1);

namespace Drupal\oe_content\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget;

/**
 * Date only widget for date and time fields, saving a fixed time.
 */
#[FieldWidget(
  id: 'oe_content_datetime_fixed_time',
  label: new TranslatableMarkup('Date only (fixed time)'),
  field_types: ['datetime'],
)]
class DateTimeFixedTimeWidget extends DateTimeDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'time' => '12:00:00',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getFieldStorageDefinition()->getSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATETIME;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time'),
      '#description' => $this->t('The time saved with the selected date, in the H:i:s format (UTC).'),
      '#default_value' => $this->getSetting('time'),
      '#required' => TRUE,
      '#size' => 8,
      '#pattern' => '([01]\d|2[0-3]):[0-5]\d:[0-5]\d',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Saved time: @time (UTC)', ['@time' => $this->getSetting('time')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Render the element like the date-only widget: no time element, no
    // timezone conversion and no fieldset wrapper.
    $element['value']['#title'] = $this->fieldDefinition->getLabel();
    $element['value']['#description'] = $this->fieldDefinition->getDescription();
    $element['value']['#date_timezone'] = DateTimeItemInterface::STORAGE_TIMEZONE;
    $element['value']['#date_time_element'] = 'none';
    $element['value']['#date_time_format'] = '';
    $key = array_search('fieldset', $element['#theme_wrappers'] ?? [], TRUE);
    if ($key !== FALSE) {
      unset($element['#theme_wrappers'][$key]);
    }

    $element['value']['#default_value'] = NULL;
    if ($items[$delta]->date) {
      // Use the date as the user sees it. This keeps the date of stored values
      // and uses the local date for default values such as "now".
      $date = clone $items[$delta]->date;
      $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
      $element['value']['#default_value'] = new DrupalDateTime($date->format('Y-m-d') . ' ' . $this->getSetting('time'), DateTimeItemInterface::STORAGE_TIMEZONE);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      if (!empty($item['value']) && $item['value'] instanceof DrupalDateTime) {
        // The element uses the storage timezone, so we only need the date.
        $item['value'] = $item['value']->format('Y-m-d') . 'T' . $this->getSetting('time');
      }
    }

    return $values;
  }

}
