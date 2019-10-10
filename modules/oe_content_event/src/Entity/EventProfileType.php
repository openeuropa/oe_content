<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Event profile type entity.
 *
 * @ConfigEntityType(
 *   id = "event_profile_type",
 *   label = @Translation("Event profile Type"),
 *   bundle_of = "event_profile",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_prefix = "event_profile_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\oe_content_event\EventProfileTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\oe_content_event\Form\EventProfileTypeForm",
 *       "add" = "Drupal\oe_content_event\Form\EventProfileTypeForm",
 *       "edit" = "Drupal\oe_content_event\Form\EventProfileTypeForm",
 *       "delete" = "Drupal\oe_content_event\Form\EventProfileTypeDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer event profile types",
 *   links = {
 *     "add-form" = "/admin/structure/event_profile_type/add",
 *     "edit-form" = "/admin/structure/event_profile_type/{event_profile_type}/edit",
 *     "delete-form" = "/admin/structure/event_profile_type/{event_profile_type}/delete",
 *     "collection" = "/admin/structure/event_profile_type",
 *   }
 * )
 */
class EventProfileType extends ConfigEntityBundleBase implements EventProfileTypeInterface {
  /**
   * The machine name of the event profile type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the event profile type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the event profile type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description): EventProfileType {
    $this->description = $description;
    return $this;
  }

}
