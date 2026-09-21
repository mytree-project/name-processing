# MyTree Name Processing

Standalone PHP library and CLI for deterministic name processing in MyTree.

The package has no Laravel dependency. Its domain and application services are framework-independent so they can be reused from CLI tools, Laravel adapters, Engine integrations, or other consumers without changing the core library.

## Scope

The package keeps distinct operations separate instead of hiding them behind one generic `normalizeName()` call:

- `normalize` — Unicode normalization, case folding, trimming, and whitespace normalization;
- `transliterate` — script conversion selected by a versioned ICU profile;
- `fold` — comparison-oriented technical folding selected by a profile;
- `variants` — candidate name variants from a versioned dataset.

Morphological analysis is intentionally outside the current scope.

The package is intentionally **name-focused**. Its `place_name` / `other` input hints and generic processing primitives do not make it a general multilingual terminology translator. For example, deterministic lexical translation such as `Arbeiter → robotnik` for an occupation is outside the currently implemented package responsibility unless a future design explicitly broadens the boundary.

Consumers may invoke name processing before any MyTree Engine run. Valid consumers include Source Acquisition, application Search, Engine integrations and standalone tools. The consuming layer decides whether a processor result is transient comparison/search data or is explicitly accepted into MyTree Acquisition state.

An accepted processor-produced transliteration or name variant may be persisted by the consuming application as a linked linguistic representation only with explicit relation/origin/provenance and normal Claim revision/history semantics. Merely running `normalize`, `fold`, `transliterate` or `variants` does not mutate Source/Mention/Claim data. Technical normalization/folding used only for matching should normally remain rebuildable derived data.


Name-processing output is **derived data**. Consumers must preserve the original source spelling separately and must not replace historical/source values with normalized, transliterated, folded, or variant forms.

## Requirements

- PHP 8.2+
- `ext-intl`
- `ext-mbstring`
- Composer for dependency installation and development tooling

Runtime code has no third-party PHP package dependency.

## Installation

For development:

```bash
composer install
```

The repository also contains a small fallback PSR-4 bootstrap, so the CLI can run from a source checkout without Composer when the required PHP extensions are installed.

## CLI

```bash
php bin/mytree-name OPERATION [--profile=PROFILE] [options] "VALUE"
```

Processing results are emitted as JSON using:

```text
mytree.name-processing.v1
```

Errors use:

```text
mytree.name-processing.error.v1
```

Examples:

```bash
php bin/mytree-name normalize "  JÓZEF  "
php bin/mytree-name transliterate --profile=cyrillic-latin "Иосиф"
php bin/mytree-name fold "Józef"
php bin/mytree-name variants --language=ru --script=Cyrl "Иосиф"
php bin/mytree-name help
```

Example result:

```json
{
  "schema": "mytree.name-processing.v1",
  "operation": "transliterate",
  "input": {
    "value": "Иосиф",
    "language": null,
    "script": null,
    "type": null
  },
  "profile": {
    "id": "cyrillic-latin",
    "version": "1.0.0"
  },
  "results": [
    {
      "value": "Iosif",
      "language": null,
      "script": "Latn",
      "type": null,
      "relation": null,
      "metadata": []
    }
  ],
  "metadata": {
    "implementation": "icu",
    "icu_id": "Cyrillic-Latin",
    "icu_version": "..."
  }
}
```

### CLI options

- `--profile=...` — processing profile; each operation has a default;
- `--language=...` — optional language hint stored in `NameInput`;
- `--script=...` — optional script hint such as `Cyrl` or `Latn`;
- `--type=given_name|surname|place_name|other` — optional name type; `variants` defaults to `given_name`;
- `--value=...` — alternative to the positional value.

Exit codes:

- `0` — success;
- `2` — invalid request, unknown operation/profile, or invalid profile configuration;
- `1` — technical/runtime failure.

## PHP API

The CLI is only an adapter. Application code should depend on the narrow processing contracts or concrete services at the composition root.

```php
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Profile\JsonProfileRepository;
use MyTree\NameProcessing\Transliteration\IcuTransliterator;

$profiles = new JsonProfileRepository(__DIR__ . '/resources/profiles');
$service = new IcuTransliterator($profiles);

$result = $service->transliterate(
    new NameInput('Иосиф', language: 'ru', script: 'Cyrl'),
    'cyrillic-latin',
);
```

All public processing services return `ProcessingResult`. This keeps the programmatic API aligned with the versioned CLI contract while allowing consumers to work with typed objects directly.

## Profiles and determinism

Profiles live under `resources/profiles/<operation>/` and have an explicit schema and semantic version. Unsupported profile schemas are rejected instead of being interpreted silently.

A processing result records the profile ID/version and, for ICU-backed operations, the ICU version. Reproducing a derived value therefore requires the same input, profile data, implementation behavior, and compatible ICU environment.

Current defaults:

| Operation | Default profile |
| --- | --- |
| normalize | `default` |
| transliterate | `cyrillic-latin` |
| fold | `latin-search` |
| variants | `genealogy-pl-ru` |

See [docs/PROFILES.md](docs/PROFILES.md).

## Variant dataset

The seed dataset is stored in:

```text
resources/variants/given-names.v1.json
```

It is deliberately small and demonstrative. Dataset membership means only that the resolver may return another spelling as `language_equivalent_candidate`; it does **not** establish identity between historical people or mentions.

The resolver depends on `VariantRepositoryInterface`, so the data can later move to another package, repository, database, or MyTree storage without changing the resolver contract.

See [docs/VARIANT_DATA.md](docs/VARIANT_DATA.md).

## Architecture

The repository follows the MyTree coding standard:

```text
Domain + Contracts
        ↑
Application services
        ↑
JSON/ICU adapters
        ↑
CLI composition root
```

Infrastructure-specific behavior remains behind narrow interfaces. See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Development and quality checks

```bash
composer test
composer analyse
composer format:check
composer qa
```

`composer qa` runs formatting checks, PHPStan, and PHPUnit. GitHub Actions runs the same quality gate on PHP 8.2 and PHP 8.4.

Tests are split into unit and integration suites under `tests/Unit` and `tests/Integration`.

## JSON contract

See [docs/JSON_CONTRACT.md](docs/JSON_CONTRACT.md). Incompatible serialized contract changes require a new schema identifier.

## Laravel / MyTree integration

Laravel or other MyTree integrations should remain thin adapters responsible for container bindings, configuration, and translating this package's typed processing results into the consuming application's use cases.

The package may be used during Source Acquisition, Search or Engine processing. Persistence and acceptance semantics belong to the consuming application: this library does not decide that a generated value is Source truth or silently write a linguistic representation into a Claim.

The standalone package must remain unaware of Eloquent, `Mention`, `Claim`, queues, Filament, or Laravel configuration classes.

## License

Apache-2.0.
