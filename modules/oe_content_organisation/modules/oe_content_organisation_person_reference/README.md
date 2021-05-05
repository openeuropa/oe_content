# OpenEuropa Organisation Person Reference

This module provides the Persons field which allows editors to reference Persons within the Organisation.
This field needs to be shipped in a dedicated module to avoid circular dependencies with the Person content type module
oe_content_person as that module depends, in turn, on the oe_content_organisation module.
