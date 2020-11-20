# OpenEuropa Content Reference Code Field

This module provides the storage configuration for a reference code field. A reference code field is a simple text field
that can be used to store codes, IDs and other reference-like information. This field is used on several corporate
content types, so it is packaged in its own, independent, module.

There is a field storage which supports unlimited number of reference codes.

## Usage

By enabling this module you just get the possibility to add a 150 length, non translatable text field named
`oe_reference_code`.

The option for unlimited number of reference codes could be implemented by enabling the `oe_reference_codes` field storage.
