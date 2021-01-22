<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_document_reference\Entity;

use Drupal\oe_content_entity\Entity\CorporateEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Document reference entity.
 *
 * @ingroup oe_content_entity_document_reference
 *
 * @ContentEntityType(
 *   id = "oe_document_reference",
 *   label = @Translation("Document reference"),
 *   label_collection = @Translation("Document references"),
 *   bundle_label = @Translation("Document reference type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\oe_content_entity\CorporateEntityListBuilder",
 *     "access" = "Drupal\oe_content_entity\CorporateEntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\oe_content_entity_document_reference\Form\DocumentReferenceForm",
 *       "add" = "Drupal\oe_content_entity_document_reference\Form\DocumentReferenceForm",
 *       "edit" = "Drupal\oe_content_entity_document_reference\Form\DocumentReferenceForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\oe_content_entity\Routing\CorporateEntityRouteProvider",
 *     },
 *   },
 *   base_table = "oe_document_reference",
 *   data_table = "oe_document_reference_field_data",
 *   revision_table = "oe_document_reference_revision",
 *   revision_data_table = "oe_document_reference_field_revision",
 *   translatable = TRUE,
 *   show_revision_ui = TRUE,
 *   admin_permission = "manage corporate content entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "bundle",
 *     "uuid" = "uuid",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *     "created" = "created",
 *     "changed" = "changed",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   bundle_entity_type = "oe_document_reference_type",
 *   field_ui_base_route = "entity.oe_document_reference_type.edit_form",
 *   links = {
 *     "canonical" = "/admin/content/oe_document_reference/{oe_document_reference}/edit",
 *     "add-page" = "/admin/content/oe_document_reference/add",
 *     "add-form" = "/admin/content/oe_document_reference/add/{oe_document_reference_type}",
 *     "edit-form" = "/admin/content/oe_document_reference/{oe_document_reference}/edit",
 *     "delete-form" = "/admin/content/oe_document_reference/{oe_document_reference}/delete",
 *     "collection" = "/admin/content/oe_document_reference",
 *   },
 *  type = {"entity"}
 * )
 */
class DocumentReference extends CorporateEntityBase implements DocumentReferenceInterface {

  /**
   * Gets the label of the entity.
   *
   * Since document reference doesn't have name field, label is generated.
   * Pattern: Document reference bundle name > referenced entity 1 label,
   * referenced entity 2 label.
   */
  public function label() {
    $bundle_label = \Drupal::entityTypeManager()
      ->getStorage('oe_document_reference_type')
      ->load($this->bundle())->label();

    $labels = $this->getReferencedEntityLabels();
    if (!empty($labels)) {
      return $bundle_label . ' > ' . $labels;
    }
    return $bundle_label;
  }

  /**
   * Gets labels of referenced entities.
   *
   * @return string
   *   Labels separated by comma.
   */
  protected function getReferencedEntityLabels(): string {
    // Load referenced entities.
    $entities = $this->referencedEntities();

    $labels = [];
    foreach ($entities as $entity) {
      if ($entity instanceof ContentEntityInterface && !($entity instanceof UserInterface)) {
        $labels[] = $entity->label();
      }
    }

    if (!$labels) {
      return '';
    }
    return implode(', ', $labels);
  }

}
