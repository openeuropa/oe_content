# OpenEuropa Organisation Reference

When correctly configured, this module will allow editors to reference organisations in very specific cases, such as
a contact information on a publication.

This module provides the "Organisation contact" bundle, whose only scope is to reference Organisation content types.

## Configuration

Simply enabling this module will not add any functionality to your site. If you want to reference organisation content
(as in, actual nodes of type "organisation"), you need enable a Contact entity reference field to reference contact
entities of type [`oe_organisation_reference`](./config/install/oe_content_entity_contact.oe_contact_type.oe_organisation_reference.yml).

For example, if you wish for your publications to reference an organisation among its contacts you need to add the
`oe_organisation_reference` bundle to the `oe_publication_contacts` field's target bundle:

```yaml
    target_bundles:
      oe_general: oe_general
      oe_organisation_reference: oe_organisation_reference
```

Incidentally the case above is supported by the OpenEuropa Theme so, when doing what described above, you'll get the
organisation you referenced properly themed as it were a contact entity.

In order for users to view, edit or delete contacts you need to explicitly grant them the right permissions.

The module provides the following permissions:

- `Contact: Create new Organisation entity`
- `Contact: Delete any Organisation entity`
- `Contact: Edit any Organisation entity`

Grant the `Contact: View any published entity` permissions to the anonymous user role in order to allow your
site's visitors to view contact entities.
