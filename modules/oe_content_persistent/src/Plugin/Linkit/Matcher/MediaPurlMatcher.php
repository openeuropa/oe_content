<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Linkit\Matcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\linkit\SubstitutionManagerInterface;
use Drupal\linkit\Utility\LinkitXss;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides specific linkit matchers for the node entity type.
 *
 * @Matcher(
 *   id = "entity:media",
 *   label = @Translation("Media"),
 *   target_entity = "media",
 *   provider = "media"
 * )
 */
class MediaPurlMatcher extends PurlEntityMatcherBase {

  /**
   * The config of PURL.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityRepositoryInterface $entity_repository, ModuleHandlerInterface $module_handler, AccountInterface $current_user, SubstitutionManagerInterface $substitution_manager, ConfigFactoryInterface $config_factory, EntityFieldManagerInterface $entity_field_manager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $database, $entity_type_manager, $entity_type_bundle_info, $entity_repository, $module_handler, $current_user, $substitution_manager, $config_factory, $entity_field_manager);
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('plugin.manager.linkit.substitution'),
      $container->get('config.factory'),
      $container->get('entity_field.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies() + [
      'module' => ['media'],
    ];
    if ($this->configuration['images']['show_thumbnail']) {
      $dependencies['module'][] = 'image';
      $dependencies['config'][] = 'image.style.' . $this->configuration['images']['thumbnail_image_style'];
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'thumbnail' => [
        'show_thumbnail' => FALSE,
        'thumbnail_image_style' => 'linkit_result_thumbnail',
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    if ($this->moduleHandler->moduleExists('image')) {
      $form['thumbnail'] = [
        '#type' => 'details',
        '#title' => $this->t('Thumbnail settings'),
        '#description' => $this->t('Extra settings for media thumbnails.'),
        '#open' => TRUE,
        '#tree' => TRUE,
      ];
      $form['thumbnail']['show_thumbnail'] = [
        '#title' => $this->t('Show thumbnail'),
        '#type' => 'checkbox',
        '#default_value' => $this->configuration['thumbnail']['show_thumbnail'],
      ];

      $form['thumbnail']['thumbnail_image_style'] = [
        '#title' => $this->t('Thumbnail image style'),
        '#type' => 'select',
        '#default_value' => $this->configuration['thumbnail']['thumbnail_image_style'],
        '#options' => image_style_options(FALSE),
        '#states' => [
          'visible' => [
            ':input[name="thumbnail[show_thumbnail]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue('thumbnail');
    if (!$values['show_thumbnail']) {
      $values['thumbnail_image_style'] = NULL;
    }
    $this->configuration['thumbnail'] = $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildDescription(EntityInterface $entity) {
    $description_array = [];

    $description_array[] = parent::buildDescription($entity);
    if ($this->configuration['thumbnail']['show_thumbnail']) {

      // Add the media thumbnail to the description.
      if (isset($entity->thumbnail)) {
        $thumbnail_display_options = [
          'type' => 'image',
          'label' => 'hidden',
          'settings' => [
            'image_style' => $this->configuration['thumbnail']['thumbnail_image_style'],
          ],
        ];
        $thumbnail = $entity->thumbnail->view($thumbnail_display_options);
        $description_array[] = (string) $this->renderer->renderPlain(
          $thumbnail
        );
      }
    }
    $description = implode('<br />', $description_array);
    return LinkitXss::descriptionFilter($description);
  }

}
