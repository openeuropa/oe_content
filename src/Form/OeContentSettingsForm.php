<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * OE Content settings configuration form.
 */
class OeContentSettingsForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['oe_content.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_content_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('oe_content.settings');

    $form['provenance_uri'] = [
      '#type' => 'url',
      '#title' => $this->t('Provenance URI'),
      '#description' => $this->t('The URI to be set as provenance URI for all RDF entities created on this site.'),
      '#default_value' => $config->get('provenance_uri'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('oe_content.settings')
      ->set('provenance_uri', $form_state->getValue('provenance_uri'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
