<?php

declare(strict_types = 1);

namespace Drupal\oe_content_person\Plugin\ConceptSubset;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\rdf_entity\RdfFieldHandlerInterface;
use Drupal\rdf_skos\ConceptSubsetPluginBase;
use Drupal\rdf_skos\Plugin\PredicateMapperInterface;

/**
 * Human sex subset for person gender.
 *
 * @ConceptSubset(
 *   id = "oe_content_person_human_sex_person",
 *   label = @Translation("Human Sex Gender"),
 *   description = @Translation("Human Sex to be used with the COM_WEB context."),
 *   predicate_mapping = TRUE,
 *   concept_schemes = {
 *     "http://publications.europa.eu/resource/authority/human-sex"
 *   }
 * )
 */
class HumanSexPerson extends ConceptSubsetPluginBase implements PredicateMapperInterface {

  /**
   * {@inheritdoc}
   */
  public function alterQuery(QueryInterface $query, $match_operator, array $concept_schemes = [], string $match = NULL): void {
    $query->condition('oe_content_person_human_sex_person', 'http://publications.europa.eu/resource/authority/use-context/COM_WEB');
  }

  /**
   * {@inheritdoc}
   */
  public function getPredicateMapping(): array {
    $mapping = [];

    $mapping['oe_content_person_human_sex_person'] = [
      'column' => 'value',
      'predicate' => ['http://lemon-model.net/lemon#context'],
      'format' => RdfFieldHandlerInterface::RESOURCE,
    ];

    return $mapping;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFieldDefinitions(): array {
    $fields = [];

    $fields['oe_content_person_human_sex_person'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Human sex contexts'))
      ->setDescription(t('Potential contexts of the human sex.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    return $fields;
  }

}
