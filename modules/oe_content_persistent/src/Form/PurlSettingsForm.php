<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration settings form for PURL.
 */
class PurlSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_content_persistent_purl_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'oe_content_persistent.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('oe_content_persistent.settings');

    $form['base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Inter institutional base url'),
      '#default_value' => $config->get('base_url'),
      '#description' => $this->t('The base URL to use for building persistent URLs'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('oe_content_persistent.settings')
      ->set('base_url', $form_state->getValue('base_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
