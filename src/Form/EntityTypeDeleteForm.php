<?php

declare(strict_types=1);

namespace Drupal\oe_content\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for content entity type deletion.
 *
 * @ingroup oe_content_entity
 */
class EntityTypeDeleteForm extends EntityDeleteForm {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EntityTypeDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity_type_id = $this->getEntity()->getEntityType()->getBundleOf();
    // @todo Why aggregate query?
    $query_aggregator = $this->entityTypeManager->getStorage($entity_type_id)->getAggregateQuery();
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $num_lists = $query_aggregator
      // We need to bypass access checks as we need to warn the user that there
      // are existing entities that prevent the deletion of this entity bundle,
      // regardless if the user can access those entities or not.
      ->accessCheck(FALSE)
      ->condition($entity_type->getKey('bundle'), $this->entity->id())
      ->count()
      ->execute();
    if ($num_lists) {
      $caption = '<p>' . $this->formatPlural(
        $num_lists,
        '%type is used by 1 %entity content entity on your site. You can not remove this type until you have removed the %entity content entity.',
        '%type is used by @count %entity content entities on your site. You may not remove %type until you have removed all of the %entity content entities.',
        [
          '%type' => $this->entity->label(),
          '%entity' => $entity_type_id,
        ]) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = ['#markup' => $caption];

      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

}
