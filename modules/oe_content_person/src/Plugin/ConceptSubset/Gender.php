<?php

declare(strict_types = 1);

namespace Drupal\oe_content_person\Plugin\ConceptSubset;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rdf_entity\RdfFieldHandlerInterface;
use Drupal\rdf_skos\ConceptSubsetPluginBase;
use Drupal\rdf_skos\Plugin\PredicateMapperInterface;

/**
 * Human sex subset for person gender.
 *
 * @ConceptSubset(
 *   id = "oe_content_gender",
 *   label = @Translation("Gender"),
 *   description = @Translation("A gender subset from the Human Sex vocabulary."),
 *   predicate_mapping = TRUE,
 *   concept_schemes = {
 *     "http://publications.europa.eu/resource/authority/human-sex"
 *   }
 * )
 */
class Gender extends ConceptSubsetPluginBase implements PredicateMapperInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterQuery(QueryInterface $query, $match_operator, array $concept_schemes = [], string $match = NULL): void {
    $query->condition('oe_content_human_sex_contexts', 'http://publications.europa.eu/resource/authority/use-context/COM_WEB');
  }

  /**
   * {@inheritdoc}
   */
  public function getPredicateMapping(): array {
    $mapping = [];

    $mapping['oe_content_human_sex_contexts'] = [
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

    $fields['oe_content_human_sex_contexts'] = BaseFieldDefinition::create('string')
      ->setLabel($this->t('Human sex contexts'))
      ->setDescription($this->t('Potential contexts of the human sex vocabulary.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    return $fields;
  }

}
