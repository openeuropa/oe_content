<?php

declare(strict_types=1);

namespace Drupal\oe_countries_test\Repository;

use Drupal\oe_corporate_countries_address\Repository\CountryRepository as OeCountryRepository;

/**
 * A country repository with definitions provided from the Publication Office.
 */
class CountryRepository extends OeCountryRepository {

  /**
   * Does the actual loading of country definitions.
   *
   * We override this function to load only a subset of countries due to
   * performance concerns during automated tests.
   *
   * @param string $locale
   *   The desired locale.
   *
   * @return array
   *   The country definitions.
   *
   * @see \Drupal\oe_corporate_countries_address\Repository\CountryRepository
   */
  protected function doLoadDefinitions($locale): array {
    $countries = $this->corporateCountryRepository->getCountries();

    // Bail out early if no country information has been returned.
    if (empty($countries)) {
      return [];
    }

    $countries_to_load = [
      'http://publications.europa.eu/resource/authority/country/ITA' => '',
      'http://publications.europa.eu/resource/authority/country/FRA' => '',
      'http://publications.europa.eu/resource/authority/country/ROU' => '',
      'http://publications.europa.eu/resource/authority/country/HUN' => '',
      'http://publications.europa.eu/resource/authority/country/BEL' => '',
      'http://publications.europa.eu/resource/authority/country/GBR' => '',
      'http://publications.europa.eu/resource/authority/country/ESP' => '',
    ];
    $countries = array_intersect_key($countries, $countries_to_load);

    /** @var \Drupal\rdf_skos\SkosEntityStorage $storage */
    $storage = $this->entityTypeManager->getStorage('skos_concept');
    $entities = $storage->loadMultiple(array_keys($countries));

    $definitions = [];
    foreach ($entities as $id => $entity) {
      $translation = $this->entityRepository->getTranslationFromContext($entity, $locale);
      $definitions[$countries[$id]['alpha-2']] = $translation->label();
    }

    uasort($definitions, function (string $a, string $b) use ($locale): int {
      return $this->transliteration->transliterate($a, $locale) <=> $this->transliteration->transliterate($b, $locale);
    });

    return $definitions;
  }

}
