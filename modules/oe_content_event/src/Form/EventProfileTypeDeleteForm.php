<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Form;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for event profile type deletion.
 *
 * @ingroup oe_content_event
 */
class EventProfileTypeDeleteForm extends EntityDeleteForm {

  /**
   * The query factory to create entity queries.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new EventProfileTypeDeleteForm object.
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
      ->get('event_profile')
      ->condition('bundle', $this->entity
        ->id())
      ->count()
      ->execute();
    if ($num_lists) {
      $caption = '<p>' . $this
        ->formatPlural(
          $num_lists,
          '%type is used by 1 event profile on your site. You can not remove this event profile type until you have removed the %type event profile.',
          '%type is used by @count event profile on your site. You may not remove %type until you have removed all of the %type event profiles.',
          [
            '%type' => $this->entity->label(),
          ]) . '</p>';
      $form['#title'] = $this
        ->getQuestion();
      $form['description'] = [
        '#markup' => $caption,
      ];
      return $form;
    }
    return parent::buildForm($form, $form_state);
  }

}
