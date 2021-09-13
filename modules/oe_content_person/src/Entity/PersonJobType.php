<?php

declare(strict_types = 1);

namespace Drupal\oe_content_person\Entity;

use Drupal\oe_content_sub_entity\Entity\SubEntityTypeBase;

/**
 * Defines the person job type entity.
 *
 * @ConfigEntityType(
 *   id = "oe_person_job_type",
 *   label = @Translation("Person job type"),
 *   bundle_of = "oe_person_job",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "oe_person_job_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
 *     "access" = "Drupal\oe_content_sub_entity\SubEntityTypeAccessControlHandler",
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
 *     "add-form" = "/admin/structure/oe_person_job_type/add",
 *     "edit-form" = "/admin/structure/oe_person_job_type/{oe_person_job_type}/edit",
 *     "delete-form" = "/admin/structure/oe_person_job_type/{oe_person_job_type}/delete",
 *     "collection" = "/admin/structure/oe_person_job_type",
 *   }
 * )
 */
class PersonJobType extends SubEntityTypeBase {}
