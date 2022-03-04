# Event speaker entity type

The "Event speaker" entity type allows editors to reference Person with individual event role.

This module provides the following bundles:

- Default event speaker bundle, useful to refer to specific Person (oe_person content type) and roles which this person plays in the event.

When used on a [entity reference revision](https://www.drupal.org/project/entity_reference_revisions) field, 
in combination with the [inline entity form](https://www.drupal.org/project/inline_entity_form) widget, 
the "Event speaker" entity type brings the additional value of being able to reference any Speakers with specific event role.

To add support to these references in created "Event speakers" fields, you have to explicitly allow needed "Default" type in the field configuration.

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
  "drupal/composite_reference": "~1.0-alpha2",
  "drupal/entity_reference_revisions": "~1.3",
}
```