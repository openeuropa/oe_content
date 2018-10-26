<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Event;

use Drupal\rdf_entity\RdfInterface;
use Drupal\rdf_skos\Entity\ConceptInterface;
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
   * The department SKOS Concept.
   *
   * @var \Drupal\rdf_skos\Entity\ConceptInterface
   */
  protected $concept;

  /**
   * The oe_department RDF entity mapped to the department term.
   *
   * @var \Drupal\rdf_entity\RdfInterface
   */
  protected $rdfEntity = NULL;

  /**
   * DepartmentReferencingEvent constructor.
   *
   * @param \Drupal\rdf_skos\Entity\ConceptInterface $concept
   *   The department SKOS Concept.
   */
  public function __construct(ConceptInterface $concept) {
    $this->concept = $concept;
  }

  /**
   * Returns the department term.
   *
   * @return \Drupal\rdf_skos\Entity\ConceptInterface
   *   The SKOS Concept.
   */
  public function getConcept(): ConceptInterface {
    return $this->concept;
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
