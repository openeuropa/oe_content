<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Event;

use Drupal\rdf_entity\RdfInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event class for department referencing dispatched events.
 */
class DepartmentReferencingEvent extends Event {

  /**
   * The event name.
   */
  const EVENT = 'oe_content.department_referencing_event';

  /**
   * The department term.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $term;

  /**
   * The oe_department RDF entity mapped to the department term.
   *
   * @var \Drupal\rdf_entity\RdfInterface
   */
  protected $rdfEntity = NULL;

  /**
   * DepartmentReferencingEvent constructor.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The department taxonomy term.
   */
  public function __construct(TermInterface $term) {
    $this->term = $term;
  }

  /**
   * Returns the department term.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The term.
   */
  public function getTerm(): TermInterface {
    return $this->term;
  }

  /**
   * Returns the department RDF entity.
   *
   * @return \Drupal\rdf_entity\RdfInterface|null
   *   The entity.
   */
  public function getRdfEntity():? RdfInterface {
    return $this->rdfEntity;
  }

  /**
   * Sets the department RDF entity.
   *
   * @param \Drupal\rdf_entity\RdfInterface $rdf_entity
   *   The entity.
   */
  public function setRdfEntity(RdfInterface $rdf_entity): void {
    $this->rdfEntity = $rdf_entity;
  }

}
