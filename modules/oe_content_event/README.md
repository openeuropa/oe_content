# OpenEuropa content event

This module provides the corporate event content type.

## Installation

Before enabling this module, make sure that the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
    "drupal/composite_reference": "~1.0-alpha2",
    "drupal/entity_reference_revisions": "~1.3",
    "drupal/field_group": "~3.2",
    "drupal/inline_entity_form": "~1.0-rc9",
    "drupal/typed_link": "~1.1",
    "openeuropa/oe_corporate_countries": "~1.0.0-beta1"
}
```

The `field_group` module requires the following patches to be applied:

```json
"patches": {
    "drupal/field_group": {
        "https://www.drupal.org/project/field_group/issues/2787179#comment-13467953": "https://www.drupal.org/files/issues/2021-08-19/2787179-highlight-html5-validation-67.patch"
    }
}
```

The `inline_entity_form` module requires the following patches to be applied:

```json
"patches": {
    "drupal/inline_entity_form": {
        "https://www.drupal.org/project/inline_entity_form/issues/2875716": "https://www.drupal.org/files/issues/2021-04-15/ief_removed_references_2875716-103.patch"
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

## Link List integration

This module provides integration with the OpenEuropa Link List component in the form of a custom Link Source Filter
plugin that allows to filter Event link lists based on their start and end dates.
To ensure proper cacheability, this plugin depends on the `openeuropa/oe_time_caching` component so make sure you
require it in your composer.json file.

## Event daterange fields replacement to daterange_timezone from version 3.x

As of 3.x the field type of the following fields ahs been changed to daterange_timezone in order to support timezones:
  - oe_event_dates
  - oe_event_online_dates
  - oe_event_registration_dates

This change has been carried out via a helper class called `EventDateRangeFieldTypeChanger` where we directly execute
the necessary MySQL commands to ensure seamless performant change in the background by maintaining the same machine-names.
The helper class can be used as follows:
```php
  $storage = new FileStorage(drupal_get_path('module', 'mymodule') . 'path to configs');
  // The new field configs with the daterange_timezone type.
  $field_storage = $storage->read('field.storage.node.oe_event_online_dates');
  $field_config = $storage->read('field.field.node.oe_event.oe_event_online_dates');
  // Call the changeFieldType() with the new daterange_timezone field configs to complete the replacement.
  EventDateRangeFieldTypeChanger::changeFieldType('oe_event_online_dates', $field_storage, $field_config);
```
