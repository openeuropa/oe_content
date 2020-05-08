<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\oe_taxonomy_types\TaxonomyTypeInterface;

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
 *     "list_builder" = "Drupal\oe_taxonomy_types\TaxonomyTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\oe_taxonomy_types\Form\TaxonomyTypeForm",
 *       "edit" = "Drupal\oe_taxonomy_types\Form\TaxonomyTypeForm",
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
 *     "handler",
 *     "handler_settings"
 *   }
 * )
 */
class TaxonomyType extends ConfigEntityBase implements TaxonomyTypeInterface {

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
   * The vocabulary reference handler ID.
   *
   * @var string
   */
  protected $handler;

  /**
   * The vocabulary reference handler settings.
   *
   * @var array
   */
  protected $handler_settings = [];

}
