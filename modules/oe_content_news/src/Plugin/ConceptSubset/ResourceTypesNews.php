<?php

declare(strict_types=1);

namespace Drupal\oe_content_news\Plugin\ConceptSubset;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\rdf_skos\ConceptSubsetPluginBase;
use Drupal\rdf_skos\Plugin\PredicateMapperInterface;
use Drupal\sparql_entity_storage\SparqlEntityStorageFieldHandlerInterface;

/**
 * Resource type subset for news types.
 *
 * @ConceptSubset(
 *   id = "oe_content_news_resource_types_news",
 *   label = @Translation("News Resource Types"),
 *   description = @Translation("Resource types to be used as the News type category."),
 *   predicate_mapping = TRUE,
 *   concept_schemes = {
 *     "http://publications.europa.eu/resource/authority/resource-type"
 *   }
 * )
 */
class ResourceTypesNews extends ConceptSubsetPluginBase implements PredicateMapperInterface {

  /**
   * {@inheritdoc}
   */
  public function alterQuery(QueryInterface $query, $match_operator, array $concept_schemes = [], ?string $match = NULL): void {
    $query->condition('oe_content_news_resource_types_news', 'http://publications.europa.eu/resource/authority/use-context/COM_NEWS');
  }

  /**
   * {@inheritdoc}
   */
  public function getPredicateMapping(): array {
    $mapping = [];

    $mapping['oe_content_news_resource_types_news'] = [
      'column' => 'value',
      'predicate' => ['http://lemon-model.net/lemon#context'],
      'format' => SparqlEntityStorageFieldHandlerInterface::RESOURCE,
    ];

    return $mapping;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFieldDefinitions(): array {
    $fields = [];

    $fields['oe_content_news_resource_types_news'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Resource type contexts'))
      ->setDescription(t('Potential contexts of the resource type.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    return $fields;
  }

}
