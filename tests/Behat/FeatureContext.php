<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Drupal\DrupalExtension\Context\DrupalContext;

/**
 * Defines step definitions that are generally useful in this project.
 */
class FeatureContext extends DrupalContext {

  /**
   * User creation and login for creating RDF entities.
   *
   * A custom step that creates a user that has the permissions to create and
   * view RDF entities of a give type.
   *
   * @Given /^I am logged in with a user that can create and view "([^"]*)" RDF entities/
   */
  public function iAmLoggedInWithUserThatCanCreateAndViewRdfEntityTypes($entity_type) {
    /** @var \Drupal\rdf_entity\RdfEntityTypeInterface[] $types */
    $types = \Drupal::entityTypeManager()->getStorage('rdf_type')->loadMultiple();
    $permission_map = [];
    foreach ($types as $id => $type) {
      $permission_map[$type->label()] = "create $id rdf entity";
    }

    if (!isset($permission_map[$entity_type])) {
      throw new \InvalidArgumentException('The provided entity type is not correct.');
    }

    $permission = $permission_map[$entity_type];
    $permissions = ['view rdf entity', 'view rdf entity overview', $permission];
    $role = $this->getDriver()->roleCreate($permissions);

    // Create user.
    $user = (object) [
      'name' => $this->getRandom()->name(8),
      'pass' => $this->getRandom()->name(16),
      'role' => $role,
    ];
    $user->mail = "{$user->name}@example.com";
    $this->userCreate($user);

    // Assign the temporary role with given permissions.
    $this->getDriver()->userAddRole($user, $role);
    $this->roles[] = $role;

    // Login.
    $this->login($user);
  }

  /**
   * Fills a date or time field at a datetime widget.
   *
   * Example: When I fill in "Start date" with the date "29-08-2016".
   * Example: When I fill in "Start date" with the time "26:59:00".
   *
   * @param string $field_group
   *   The field component's label.
   * @param string $date_component
   *   The field to be filled.
   * @param string $value
   *   The value of the field.
   *
   * @throws \Exception
   *    Thrown when more than one elements match the given field in the given
   *    field group.
   *
   * @When I fill in :field_group with the :date_component :value
   */
  public function fillDateField($field_group, $date_component, $value) {
    $field_selectors = $this->findDateFields($field_group);
    if (count($field_selectors) > 1) {
      throw new \Exception("More than one elements were found.");
    }
    $field_selector = reset($field_selectors);
    $field_selector->fillField(ucfirst($date_component), $value);
  }

  /**
   * Finds a datetime field.
   *
   * @param string $field
   *   The field name.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The elements found.
   *
   * @throws \Exception
   *   Thrown when the field was not found.
   */
  public function findDateFields($field) {
    $field_selectors = $this->getSession()->getPage()->findAll('css', '.field--widget-datetime-timestamp');
    $field_selectors = array_filter($field_selectors, function ($field_selector) use ($field) {
      return $field_selector->has('named', ['content', $field]);
    });
    if (empty($field_selectors)) {
      throw new \Exception("Date field {$field} was not found.");
    }
    return $field_selectors;
  }

}
