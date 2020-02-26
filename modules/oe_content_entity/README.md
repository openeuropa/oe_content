# OpenEuropa corporate content entities

This module and its sub-modules ship a set of corporate content entity types that are used to collect information
of different content classes on corporate content types.

Below the provided corporate content entities and bundles. In order to use a specific corporate content entity
type enable the related module.

## Contact entity type

The contact entity type is to be used when collecting information about different types of contacts, 
such as a press contact. In order to have it available on your site enable the `oe_content_entity_contact` 
module, this will provide the following bundles:

- General contact bundle, useful to collect generic contact information
- Press contact bundle, useful to collect press related information

In order users to view, edit or delete contacts you need to explicitly grant then the right permissions.

The module provides the following permissions:

- `Contact: Access canonical page`
- `Contact: Access overview page`
- `Contact: Create new General entity`
- `Contact: Create new Press entity`
- `Contact: Delete any General entity`
- `Contact: Delete any Press entity`
- `Contact: Edit any General entity`
- `Contact: Edit any Press entity`
- `Contact: View any published entity`
- `Contact: View any unpublished entity`

Grant the `Contact: View any published entity` permissions to anonymous user role in order to allow your 
site's visitors to view contact entities.

## Organisation entity type

The organisation entity type is to be used when collecting information about different types of organisations, 
such as a project partners. In order to have it available on your site enable the `oe_content_entity_organisation` 
module, this will provide the following bundles:

- Partner organisation bundle, useful to collect project partner's name, website, image

In order users to view, edit or delete organisations you need to explicitly grant them the right permissions.

The module provides the following permissions:

- `Organisation: Access canonical page`
- `Organisation: Access overview page`
- `Organisation: Create new Partner entity`
- `Organisation: Delete any Partner entity`
- `Organisation: Edit any Partner entity`
- `Organisation: View any published entity`
- `Organisation: View any unpublished entity`

Grant the `Organisation: View any published entity` permission to anonymous user role in order to allow your 
site's visitors to view organisation entities.

## Venue entity type

The venue entity type is to be used when collecting information about different types of venues, 
such as a building or a conference room. In order to have it available on your site enable the `oe_content_entity_venue` 
module, this will provide the following bundles:

- Default venue bundle, useful to collect default venue's information such as name, address, capacity, room

In order users to view, edit or delete venues you need to explicitly grant them the right permissions.

The module provides the following permissions:

- `Venue: Access canonical page`
- `Venue: Access overview page`
- `Venue: Create new Default entity`
- `Venue: Edit any Default entity`
- `Venue: Delete any Default entity`
- `Venue: View any published entity`
- `Venue: View any unpublished entity`

Grant the `Venue: View any published entity` permission to anonymous user role in order to allow your 
site's visitors to view venue entities.
