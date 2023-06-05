<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Plugin\facets\processor;

use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\facets\Exception\InvalidProcessorException;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AddressCountryLabelProcessor.
 *
 * @FacetsProcessor(
 *   id = "address_country_label",
 *   label = @Translation("Transform country code to label"),
 *   description = @Translation("Display the country label instead of its code."),
 *   stages = {
 *     "build" = 5
 *   }
 * )
 */
class AddressCountryLabelProcessor extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The country manager.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * AddressCountryLabelProcessor constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   The country manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CountryManagerInterface $country_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->countryManager = $country_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('country_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function supportsFacet(FacetInterface $facet) {
    /** @var \Drupal\Core\TypedData\DataDefinitionInterface $dataDefinition */
    $dataDefinition = $facet->getDataDefinition();

    return $dataDefinition->getDataType() === 'field_item:address';
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    /** @var \Drupal\Core\TypedData\DataDefinitionInterface $dataDefinition */
    $dataDefinition = $facet->getDataDefinition();

    $property = NULL;
    /** @var \Drupal\Core\TypedData\DataDefinitionInterface $definition */
    foreach ($dataDefinition->getPropertyDefinitions() as $key => $definition) {
      if ($dataDefinition->getDataType() === 'field_item:address') {
        $property = $key;
        break;
      }
    }

    if ($property === NULL) {
      throw new InvalidProcessorException((string) $this->t("Field doesn't have a country definition, so this processor doesn't work."));
    }

    $countryList = $this->countryManager->getList();
    foreach ($results as $index => $result) {
      $code = strtoupper($result->getRawValue());
      $results[$index]->setDisplayValue($countryList[$code] ?? $code);
    }
    return $results;
  }

}
