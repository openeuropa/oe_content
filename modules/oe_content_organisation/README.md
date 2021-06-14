# OpenEuropa Organisation Content

This module provides the corporate organisation content type.

## Referencing persons from an organisation

According to DG COMM's requirements, an organisation should be able to reference multiple persons. Unfortunately, since
we already had the `oe_content_person` module depending on the `oe_content_organisation`, we cannot store an entity
reference field (referencing persons) within the `oe_content_organisation` module.

For this reason we have created the `oe_content_organisation_person_reference` sub-module.

If you want to comply with DG COMM requirements, install the `oe_content_organisation_person_reference` module only
after you have installed the `oe_content_organisation`, and export your site's configuration: this will avoid the
circular dependency problem.

If, on the other hand, you have a module that needs to depend on the `oe_content_organisation` module, and it needs to
provide a reference to persons, then make sure that your module depends only on
`oe_content_organisation_person_reference`, and not on `oe_content_organisation`, or you will encounter the circular
dependency issue when enabling your module.

## Installation

Before enabling this module, make sure that the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
    "drupal/composite_reference": "~1.0-alpha2",
    "drupal/entity_reference_revisions": "~1.3",
    "drupal/field_group": "~3.0",
    "drupal/inline_entity_form": "~1.0-rc8"
}
```

The `field_group` module requires the following patches to be applied:

```json
"patches": {
    "drupal/field_group": {
        "https://www.drupal.org/project/field_group/issues/2787179#comment-13467953": "https://www.drupal.org/files/issues/2020-02-17/2787179-highlight-html5-validation-45.patch",
        "https://www.drupal.org/node/3072732": "https://www.drupal.org/files/issues/2019-08-06/3072732-6.patch"
    }
}
```

The `inline_entity_form` module requires the following patches to be applied:

```json
"patches": {
    "drupal/inline_entity_form": {
        "https://www.drupal.org/project/inline_entity_form/issues/2875716": "https://www.drupal.org/files/issues/2020-11-05/ief_removed_references_2875716-89.patch"
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
