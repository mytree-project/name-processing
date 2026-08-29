# Variant data

The MVP stores name-variant data in the same repository but isolates it behind `VariantRepositoryInterface`.

## Why it is not a separate repository yet

The data model is not considered stable enough. We may later need fields such as period, region, confession/tradition, source, strength, or relation type. Keeping the seed dataset local avoids prematurely fixing a cross-repository compatibility contract.

## Dataset contract

A dataset contains versioned groups. Each group contains variants with explicit language/script/type metadata.

Example:

```json
{
  "id": "joseph",
  "variants": [
    {"value": "Józef", "language": "pl", "script": "Latn", "type": "given_name"},
    {"value": "Иосиф", "language": "ru", "script": "Cyrl", "type": "given_name"}
  ]
}
```

Membership in one group means only that the resolver may return the forms as `language_equivalent_candidate`. It does not prove that two historical mentions refer to the same person.

## Future extraction

The code should not change if the JSON file is later replaced by a separate package or repository. Only the `VariantRepositoryInterface` implementation/configuration should change.
