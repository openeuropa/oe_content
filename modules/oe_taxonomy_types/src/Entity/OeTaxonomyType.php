<?php

namespace Drupal\oe_taxonomy_types\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\oe_taxonomy_types\OeTaxonomyTypeInterface;

/**
 * Defines the taxonomy type entity type.
 *
 * @ConfigEntityType(
 *   id = "oe_taxonomy_type",
 *   label = @Translation("Taxonomy type"),
 *   label_collection = @Translation("Taxonomy types"),
 *   label_singular = @Translation("taxonomy type"),
 *   label_plural = @Translation("taxonomy types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count taxonomy type",
 *     plural = "@count taxonomy types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\oe_taxonomy_types\OeTaxonomyTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\oe_taxonomy_types\Form\OeTaxonomyTypeForm",
 *       "edit" = "Drupal\oe_taxonomy_types\Form\OeTaxonomyTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "oe_taxonomy_type",
 *   admin_permission = "administer oe_taxonomy_type",
 *   links = {
 *     "collection" = "/admin/structure/oe-taxonomy-type",
 *     "add-form" = "/admin/structure/oe-taxonomy-type/add",
 *     "edit-form" = "/admin/structure/oe-taxonomy-type/{oe_taxonomy_type}",
 *     "delete-form" = "/admin/structure/oe-taxonomy-type/{oe_taxonomy_type}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "vocabulary_type"
 *   }
 * )
 */
class OeTaxonomyType extends ConfigEntityBase implements OeTaxonomyTypeInterface {

  /**
   * The taxonomy type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The taxonomy type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The taxonomy type description.
   *
   * @var string
   */
  protected $description;

  /**
   * The type of the vocabulary being referenced.
   *
   * @var string
   */
  protected $vocabulary_type;

}
