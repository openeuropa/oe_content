# OpenEuropa Person sub-entity

This module provides the "Person" sub-entity type, along with the following set of person bundles:

- Political leader, that can reference multiple "EU political leader names" SKOS terms
- Person, that can reference multiple "Person" content type nodes

When used on a [entity reference revision](https://www.drupal.org/project/entity_reference_revisions) field, in combination
with the [inline entity form](https://www.drupal.org/project/inline_entity_form) widget, the "Person" entity type brings
the additional value of being able to arrange heterogeneous persons (as in: references to different entity types and
bundle or external/internal links) in the needed order.

To add support to these references in created "Persons" fields, you have to explicitly allow needed person types in the field configuration.

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
  "drupal/composite_reference": "^2.1"
  "drupal/entity_reference_revisions": "^1.8",
}
```

