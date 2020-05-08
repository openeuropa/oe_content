# OpenEuropa content project

This module provides the corporate project content type.

## Installation

Before enabling this module, make sure that the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
    "drupal/address": "~1.7",
    "drupal/entity_reference_revisions": "~1.3",
    "drupal/field_group": "~3.0",
    "drupal/inline_entity_form": "~1.0-rc3",
    "drupal/typed_link": "~1.0",
}
```

The `field_group` and `typed_link` modules requires the following patches to be applied:

```json
"patches": {
    "drupal/field_group": {
        "https://www.drupal.org/project/field_group/issues/2787179#comment-13467953": "https://www.drupal.org/files/issues/2020-02-17/2787179-highlight-html5-validation-45.patch"
    },
    "drupal/typed_link": {
        "https://www.drupal.org/project/typed_link/issues/3085826": "https://www.drupal.org/files/issues/2019-10-04/typed_link-3085826-2.patch",
        "https://www.drupal.org/project/typed_link/issues/3085817": "https://www.drupal.org/files/issues/2019-10-07/typed_link-3085817-3.patch"
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
