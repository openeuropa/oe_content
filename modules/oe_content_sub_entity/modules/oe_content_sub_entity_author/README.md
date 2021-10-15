# Author entity type

This module ships a set of various "Author" types which are used to introduce entities like Corporate body, Person, Organisation, or simple links to internal or external content as authors of the publications.

After enabling of module you should see following "Author" types:
- Corporate body (with reference some Corporate body term)
- Person (with reference to some existing Person content)
- Organisation (with reference to some existing Organisation content)
- Link (with link field where you can add internal or external link)

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
  "drupal/composite_reference": "~1.0-alpha2",
  "drupal/entity_reference_revisions": "~1.3",
}
```

## Usage
When used on a [entity reference revision](https://www.drupal.org/project/entity_reference_revisions) field, in combination with the [inline entity form](https://www.drupal.org/project/inline_entity_form) widget, the "Author" entity type brings the additional value of being able to arrange heterogeneous authors (as in: references to different entity types and bundle or external/internal links) in the needed order.
To add support to these references in created "Authors" fields, you have to explicitly allow needed author types in the field configurations.
