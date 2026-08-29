# Processing profiles

Processing behavior that affects semantic output is stored in versioned JSON profiles under:

```text
resources/profiles/<operation>/<profile-id>.json
```

## Schema

Current profiles use:

```text
mytree.name-processing-profile.v1
```

A profile contains:

```json
{
  "schema": "mytree.name-processing-profile.v1",
  "id": "default",
  "version": "1.0.0",
  "operation": "normalize",
  "config": {}
}
```

`JsonProfileRepository` validates the schema, path identity, operation, version, and configuration object. Unsupported schemas are rejected explicitly.

## Compatibility rule

Changing implementation code does not automatically require a new profile schema. However, an incompatible change to the serialized profile structure requires a new schema identifier.

A behavior change that can alter processing output should also change the relevant profile version or otherwise be captured by a versioned implementation/release so results remain reproducible.

## Built-in profiles

| Operation | Profile | Purpose |
| --- | --- | --- |
| normalize | `default` | NFC, whitespace normalization, trim, Unicode case folding |
| transliterate | `cyrillic-latin` | ICU Cyrillic-to-Latin transliteration |
| fold | `latin-search` | Latin-to-ASCII comparison folding |
| variants | `genealogy-pl-ru` | seed variant dataset lookup |
