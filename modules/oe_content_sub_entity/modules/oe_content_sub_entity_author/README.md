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

## Update 'oe_author' field to the sub-entity reference field 'oe_authors'

The module ships the AuthorSkosUpdater service to migrate the values from 'oe_author' skos reference field
to the new 'oe_authors' sub-entity reference field. This operation can be performed within an update hook
that uses batch operation to loop over nodes of specific content types as follows:
```php

/**
 * Update the nodes with author field.
 */
function hook_post_update_00001(array &$sandbox) {
  if (!isset($sandbox['total'])) {
    $content_types = [
      'oe_event',
      'oe_list_page',
      'oe_page',
      'oe_news',
      'oe_policy',
      'oe_publication',
    ];
    // Get all the nodes which have author.
    $ids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->condition('type', $content_types, 'IN')
      ->exists('oe_author')
      ->allRevisions()
      ->execute();

    if (!$ids) {
      return t('No nodes had to be updated with author values.');
    }

    $sandbox['ids'] = array_unique($ids);
    $sandbox['total'] = count($sandbox['ids']);
    $sandbox['current'] = 0;
  }

  // We process one node at the time.
  $id = array_pop($sandbox['ids']);
  $node = Node::load($id);
  $updater = \Drupal::service('oe_content_sub_entity_author.skos_updater');
  $updater->updateNode($node);

  $sandbox['current']++;
  $sandbox['#finished'] = empty($sandbox['total']) ? 1 : ($sandbox['current'] / $sandbox['total']);

  if ($sandbox['#finished'] === 1) {
    return t('A total of @updated nodes have been updated with author values.', ['@updated' => $sandbox['current']]);
  }
}
```
