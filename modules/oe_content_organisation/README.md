# OpenEuropa Organisation Content

This module provides the corporate organisation content type.

## Installation

Before enabling this module, make sure that the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
    "drupal/composite_reference": "~1.0-alpha2",
    "drupal/entity_reference_revisions": "~1.3",
    "drupal/field_group": "~3.0",
    "drupal/inline_entity_form": "~1.0-rc9"
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
