<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_document_reference\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityTypeBase;

/**
 * Defines the document reference type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_document_reference_type",
 *   label = @Translation("Document reference type"),
 *   bundle_of = "oe_document_reference",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_document_reference_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\oe_content\Entity\EntityTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\oe_content\Form\EntityTypeForm",
 *       "add" = "Drupal\oe_content\Form\EntityTypeForm",
 *       "edit" = "Drupal\oe_content\Form\EntityTypeForm",
 *       "delete" = "Drupal\oe_content\Form\EntityTypeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer sub entity types",
 *   links = {
 *     "add-form" = "/admin/structure/oe_document_reference_type/add",
 *     "edit-form" = "/admin/structure/oe_document_reference_type/{oe_document_reference_type}/edit",
 *     "delete-form" = "/admin/structure/oe_document_reference_type/{oe_document_reference_type}/delete",
 *     "collection" = "/admin/structure/oe_document_reference_type",
 *   }
 * )
 */
class DocumentReferenceType extends SubEntityTypeBase {}
