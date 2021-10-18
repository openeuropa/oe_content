# OpenEuropa Author sub-entity

This module provides the "Author" sub-entity type, along with the following set of author bundles:

- Corporate body, that can reference multiple Corporate body SKOS terms
- Person, that can reference multiple "Person" content type nodes
- Organisation, that can reference multiple "Organisation" content type
- Link, which provides a simple, multiple value, link field

When used on a [entity reference revision](https://www.drupal.org/project/entity_reference_revisions) field, in combination
with the [inline entity form](https://www.drupal.org/project/inline_entity_form) widget, the "Author" entity type brings
the additional value of being able to arrange heterogeneous authors (as in: references to different entity types and
bundle or external/internal links) in the needed order.

To add support to these references in created "Authors" fields, you have to explicitly allow needed author types in the field configuration.

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
  "drupal/composite_reference": "~1.0-alpha2",
  "drupal/entity_reference_revisions": "~1.3",
}
```
