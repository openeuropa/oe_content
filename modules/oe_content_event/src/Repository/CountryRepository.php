<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Repository;

use Drupal\address\Repository\CountryRepository as AddressCountryRepository;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rdf_entity\Database\Driver\sparql\ConnectionInterface;

/**
 * A country repository with definitions provided from the Publication Office.
 */
class CountryRepository extends AddressCountryRepository {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The SPARQL endpoint.
   *
   * @var \Drupal\rdf_entity\Database\Driver\sparql\ConnectionInterface
   */
  protected $sparql;

  /**
   * Creates a CountryRepository instance.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\rdf_entity\Database\Driver\sparql\ConnectionInterface $sparql
   *   The SPARQL endpoint.
   */
  public function __construct(CacheBackendInterface $cache, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, ConnectionInterface $sparql) {
    parent::__construct($cache, $language_manager);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->sparql = $sparql;
  }

  /**
   * {@inheritdoc}
   */
  protected function loadDefinitions($locale) {
    if (isset($this->definitions[$locale])) {
      return $this->definitions[$locale];
    }

    $cache_key = 'oe_content_event.op_countries.' . $locale;
    if ($cached = $this->cache->get($cache_key)) {
      $this->definitions[$locale] = $cached->data;
    }
    else {
      $this->definitions[$locale] = $this->doLoadDefinitions($locale);
      $this->cache->set($cache_key, $this->definitions[$locale], CacheBackendInterface::CACHE_PERMANENT, ['countries']);
    }

    return $this->definitions[$locale];
  }

  /**
   * Does the actual loading of country definitions.
   *
   * @param string $locale
   *   The desired locale.
   *
   * @return array
   *   The country definitions.
   */
  protected function doLoadDefinitions($locale) {
    $query = <<<SPARQL
SELECT DISTINCT ?id, ?iso, ?deprecated
WHERE {
  ?id <http://www.w3.org/2004/02/skos/core#inScheme> <http://publications.europa.eu/resource/authority/country> .
  ?id a <http://www.w3.org/2004/02/skos/core#Concept> .
  ?id <http://publications.europa.eu/ontology/authority/authority-code> ?iso .
  ?id <http://publications.europa.eu/ontology/authority/deprecated> ?deprecated .
}
ORDER BY asc(?iso)
SPARQL;

    $results = $this->sparql->query($query);

    // Create a mapping between ISO 3166-1 alpha-2 and alpha-3 country codes.
    $code_mappings = $this->getIsoCodeMappings();

    // Do a first pass on the query results and collect all the country URIs,
    // keyed by alpha2 code. This will allow to later perform a single load
    // query in the SPARQL storage.
    $definitions = [];
    foreach ($results as $item) {
      $alpha3 = $item->iso->getValue();
      if (!isset($code_mappings[$alpha3])) {
        continue;
      }

      $definitions[$code_mappings[$alpha3]] = $item->id->getUri();
    }

    /** @var \Drupal\rdf_skos\SkosEntityStorage $storage */
    $storage = $this->entityTypeManager->getStorage('skos_concept');
    $entities = $storage->loadMultiple($definitions, ['country']);
    foreach ($definitions as $alpha2 => $uri) {
      if (!isset($entities[$uri])) {
        continue;
      }

      $translation = $this->entityRepository->getTranslationFromContext($entities[$uri], $locale);
      $definitions[$alpha2] = $translation->label();
    }

    asort($definitions);

    return $definitions;
  }

  /**
   * Returns a mapping between ISO 3166-1 alpha-2 and alpha-3 country codes.
   *
   * @return array
   *   The alpha-3 codes, keyed by alpha-2.
   */
  protected function getIsoCodeMappings(): array {
    $filename = drupal_get_path('module', 'oe_content_event') . '/resources/country-code-mappings.json';
    $code_mappings = Json::decode(file_get_contents($filename));

    return is_array($code_mappings) ? array_flip($code_mappings) : [];
  }

}
