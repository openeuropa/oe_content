# OpenEuropa Call for tenders Content

This module provides the corporate "Call for tenders" (oe_call_tenders) content type.

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
    "drupal/entity_reference_revisions": "~1.3",
    "drupal/field_group": "~3.0",
}
```

The `field_group` module requires the following patches to be applied:

```json
"patches": {
    "drupal/field_group": {
        "https://www.drupal.org/project/field_group/issues/2787179#comment-13467953": "https://www.drupal.org/files/issues/2020-02-17/2787179-highlight-html5-validation-45.patch"
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

### Upgrade from 1.12.0

We add maxlength settings for the oe_content_short_title, oe_summary, oe_teaser and title fields:

- `oe_content_short_title`: 170 characters
- `oe_summary`: 250 characters
- `oe_teaser`: 150 characters
- `title`: 170 characters

As a result content loss might occur for existing content while editing if the length of above fields
is greater than the specified length.
