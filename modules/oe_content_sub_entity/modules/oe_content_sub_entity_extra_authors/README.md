# Extra Author sub-entity bundles

This module ships a set of additional "Author" types which are used to introduce entities like Person, Organisation, or simple links to internal or external content as extra authors of the publications.

After enabling of module you should see additional "Author" types:
- Person (with reference to some existing Person content)
- Organisation (with reference to some existing Organisation content)
- Link (with link field where you can add internal or external link)

## Installation

Before enabling this module, make sure the following modules are present in your codebase by adding them to your
`composer.json` and by running `composer update`:

```json
"require": {
  "drupal/composite_reference": "~1.0-alpha2",
}
```

## Usage
To add support to these references in existing "Authors" fields, you have to explicitly allow these author types in the field configurations.
