<?php

namespace Drupal\oe_taxonomy_types\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\oe_taxonomy_types\OeTaxonomyTypeAssociationInterface;

/**
 * Defines the taxonomy type association entity type.
 *
 * @ConfigEntityType(
 *   id = "oe_taxonomy_type_association",
 *   label = @Translation("Taxonomy type association"),
 *   label_collection = @Translation("Taxonomy type associations"),
 *   label_singular = @Translation("taxonomy type association"),
 *   label_plural = @Translation("taxonomy type associations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count taxonomy type association",
 *     plural = "@count taxonomy type associations",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\oe_taxonomy_types\OeTaxonomyTypeAssociationListBuilder",
 *     "form" = {
 *       "add" = "Drupal\oe_taxonomy_types\Form\OeTaxonomyTypeAssociationForm",
 *       "edit" = "Drupal\oe_taxonomy_types\Form\OeTaxonomyTypeAssociationForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "oe_taxonomy_type_association",
 *   admin_permission = "administer oe_taxonomy_type_association",
 *   links = {
 *     "collection" = "/admin/structure/oe-taxonomy-type-association",
 *     "add-form" = "/admin/structure/oe-taxonomy-type-association/add",
 *     "edit-form" = "/admin/structure/oe-taxonomy-type-association/{oe_taxonomy_type_association}",
 *     "delete-form" = "/admin/structure/oe-taxonomy-type-association/{oe_taxonomy_type_association}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "name",
 *     "field",
 *     "widget_type",
 *     "taxonomy_type",
 *     "cardinality",
 *     "required",
 *     "predicate",
 *     "help_text"
 *   }
 * )
 */
class OeTaxonomyTypeAssociation extends ConfigEntityBase implements OeTaxonomyTypeAssociationInterface {

  /**
   * The taxonomy type association ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The taxonomy type association label.
   *
   * @var string
   */
  protected $label;

  /**
   * The taxonomy type machine name.
   *
   * @var string
   */
  protected $name;

  /**
   * The field instance ID to which this association is made.
   *
   * @var string
   */
  protected $field;

  /**
   * The widget type to use.
   *
   * @var string
   */
  protected $widget_type;

  /**
   * The taxonomy type ID.
   *
   * @var string
   */
  protected $taxonomy_type;

  /**
   * The association cardinality.
   *
   * @var int
   */
  protected $cardinality;

  /**
   * Flag indicating whether the field is required.
   *
   * @var bool
   */
  protected $required;

  /**
   * The association predicate.
   *
   * @var string
   */
  protected $predicate;

  /**
   * The help text to show on the widget.
   *
   * @var string
   */
  protected $help_text;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getTaxonomyType() . '.' . $this->getField() . '.' . $this->getName();
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): ?string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getField(): ?string {
    return $this->field;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetType(): ?string {
    return $this->widget_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getTaxonomyType(): ?string {
    return $this->taxonomy_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getCardinality(): ?int {
    return $this->cardinality;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired(): ?bool {
    return $this->required;
  }

  /**
   * {@inheritdoc}
   */
  public function getPredicate(): ?string {
    return $this->predicate;
  }

  /**
   * {@inheritdoc}
   */
  public function getHelpText(): ?string {
    return $this->help_text;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    if ($this->isNew()) {
      $this->id = $this->id();
    }

    parent::preSave($storage);
  }


}
