<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_document_reference\Entity;

use Drupal\oe_content_entity\Entity\CorporateEntityTypeBase;

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
 *     "list_builder" = "Drupal\oe_content_entity\CorporateEntityTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\oe_content_entity\Form\CorporateEntityTypeForm",
 *       "add" = "Drupal\oe_content_entity\Form\CorporateEntityTypeForm",
 *       "edit" = "Drupal\oe_content_entity\Form\CorporateEntityTypeForm",
 *       "delete" = "Drupal\oe_content_entity\Form\CorporateEntityTypeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "manage corporate content entity types",
 *   links = {
 *     "add-form" = "/admin/structure/oe_document_reference_type/add",
 *     "edit-form" = "/admin/structure/oe_document_reference_type/{oe_document_reference_type}/edit",
 *     "delete-form" = "/admin/structure/oe_document_reference_type/{oe_document_reference_type}/delete",
 *     "collection" = "/admin/structure/oe_document_reference_type",
 *   }
 * )
 */
class DocumentReferenceType extends CorporateEntityTypeBase {}
