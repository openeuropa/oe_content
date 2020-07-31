<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration settings form for PURL.
 */
class PurlSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a PurlSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

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

    $form['supported_entity_types'] = [
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#options' => $this->getEntityTypeOptions(),
      '#default_value' => $config->get('supported_entity_types'),
      '#title' => $this->t('Supported entity types'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('oe_content_persistent.settings')
      ->set('base_url', $form_state->getValue('base_url'))
      ->set('supported_entity_types', $form_state->getValue('supported_entity_types'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Returns an array of available entity types in the site.
   *
   * @return array
   *   The available entity types keyed by ID.
   */
  protected function getEntityTypeOptions() : array {
    $entity_type_definitions = $this->entityTypeManager->getDefinitions();
    $entity_type_options = [];
    foreach ($entity_type_definitions as $definition) {
      if ($definition instanceof ContentEntityTypeInterface) {
        $entity_type_options[$definition->id()] = $definition->getLabel();
      }
    }
    return $entity_type_options;
  }

}
