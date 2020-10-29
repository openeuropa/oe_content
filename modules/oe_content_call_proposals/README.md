# OpenEuropa content Call for proposals

This module provides the Call for proposals content type.

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
    "drupal/entity_reference_revisions": "~1.3",
    "drupal/composite_reference": "~1.0-alpha2",
    "drupal/inline_entity_form": "~1.0-rc7",
}
```

The `inline_entity_form` module requires the following patches to be applied:

```json
"patches": {
    "drupal/inline_entity_form": {
        "https://www.drupal.org/project/inline_entity_form/issues/3162384": "https://www.drupal.org/files/issues/2020-08-13/fixed_duplicate_rows-3162384-16.patch",
        "https://www.drupal.org/project/inline_entity_form/issues/2842744#comment-13775778": "https://www.drupal.org/files/issues/2020-08-04/inline_entity_form-no_label_required_field_with_no_entries-2842744-27-D8.patch"
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
