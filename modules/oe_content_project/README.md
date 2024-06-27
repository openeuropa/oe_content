# OpenEuropa content project

This module provides the corporate project content type.

## Installation

Before enabling this module, make sure that the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
    "drupal/composite_reference": "~1.0@alpha",
    "drupal/entity_reference_revisions": "~1.3",
    "drupal/field_group": "~3.2",
    "drupal/inline_entity_form": "~1.0-rc9",
}
```

The `field_group` module requires the following patches to be applied:

```json
"patches": {
    "drupal/field_group": {
        "https://www.drupal.org/project/field_group/issues/2787179#comment-13467953": "https://www.drupal.org/files/issues/2021-08-19/2787179-highlight-html5-validation-67.patch"
    },
}
```

The `inline_entity_form` module requires the following patches to be applied:

```json
"patches": {
    "drupal/inline_entity_form": {
        "https://www.drupal.org/project/inline_entity_form/issues/2875716": "https://www.drupal.org/files/issues/2022-07-20/ief_removed_references_2875716-104.patch"
    }
}
```

In order to apply the patches above add the following to your project's `composer.json` file:

```json
"require": {
    "cweagans/composer-patches": "^1.6"
},
"extra": {
    "enable-patching": true
}
```

For more information check `cweagans/composer-patches` documentation [here](https://github.com/cweagans/composer-patches).

## Replacing Project budget fields and removing previous float type fields as of 4.x version

As of 4.x the following float type fields had been removed:
- oe_project_budget
- oe_project_budget_eu

These fields had been replaced by two decimal type fields:
- oe_project_eu_budget
- oe_project_eu_contrib
