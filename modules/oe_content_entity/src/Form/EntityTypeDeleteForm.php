<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity\Form;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for custom content entity type deletion.
 *
 * @ingroup oe_content_entity
 */
class EntityTypeDeleteForm extends EntityDeleteForm {

  /**
   * The query factory to create entity queries.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new EntityTypeDeleteForm object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query object.
   */
  public function __construct(QueryFactory $query_factory) {
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.query'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $num_lists = $this->queryFactory
      ->get($this->getEntity()->getEntityType()->getBundleOf())
      ->condition('bundle', $this->entity->id())
      ->count()
      ->execute();
    if ($num_lists) {
      $caption = '<p>' . $this->formatPlural(
        $num_lists,
        '%type is used by 1 %entity content entity on your site. You can not remove this type until you have removed the %entity content entity.',
        '%type is used by @count %entity content entities on your site. You may not remove %type until you have removed all of the %entity content entities.',
        [
          '%type' => $this->entity->label(),
          '%entity' => $this->getEntity()->getEntityType()->getBundleOf(),
        ]) . '</p>';
      $form['#title'] = $this->getQuestion();
      $form['description'] = ['#markup' => $caption];

      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

}
