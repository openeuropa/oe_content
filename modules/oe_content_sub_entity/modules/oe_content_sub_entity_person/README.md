# OpenEuropa Person sub-entity

This module provides the "Person" sub-entity type, and a reference field to reference node with person bundles.

When used on a [entity reference revision](https://www.drupal.org/project/entity_reference_revisions) field, in combination
with the [inline entity form](https://www.drupal.org/project/inline_entity_form) widget, the "Person" entity type brings
the additional value of being able to arrange heterogeneous persons (as in: references to different entity types and
bundle or external/internal links) in the needed order.

To add support to these references in created "Persons" fields, you have to explicitly allow needed person types in the field configuration.

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
  "drupal/composite_reference": "^2.1"
  "drupal/entity_reference_revisions": "^1.8",
}
```

## Migrate 'oe_persons' field to the sub-entity reference field 'oe_persons_reference'

The module ships the PersonNodeUpdater service to migrate the values from 'oe_persons' node reference field
to the new 'oe_persons_reference' sub-entity reference field. This operation can be performed within an update hook
that uses batch operation to loop over nodes of specific content types as follows:
```php

/**
 * Update the nodes with person field.
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
    // Get all the nodes which have person.
    $ids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->condition('type', $content_types, 'IN')
      ->exists('oe_persons')
      ->allRevisions()
      ->accessCheck(FALSE)
      ->execute();

    if (!$ids) {
      return t('No nodes had to be updated with person values.');
    }

    $sandbox['ids'] = array_unique($ids);
    $sandbox['total'] = count($sandbox['ids']);
    $sandbox['current'] = 0;
  }

  // We process one node at the time.
  $id = array_pop($sandbox['ids']);
  $node = Node::load($id);
  $updater = \Drupal::service('oe_content_sub_entity_person.node_updater');
  $updater->updateNode($node);

  $sandbox['current']++;
  $sandbox['#finished'] = empty($sandbox['total']) ? 1 : ($sandbox['current'] / $sandbox['total']);

  if ($sandbox['#finished'] === 1) {
    return t('A total of @updated nodes have been updated with person values.', ['@updated' => $sandbox['current']]);
  }
}
```

