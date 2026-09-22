# Architecture

## Dependency direction

```text
Domain + Contracts
        ↑
Application
        ↑
Profile / Variants / ICU adapters
        ↑
CLI composition root
```

The CLI chooses an **operation** and optionally a **profile**. It does not expose implementation class names or framework concerns.

`DefaultRegistryFactory` is the standalone composition root: it creates concrete JSON repositories and ICU-backed services, then injects them into operation adapters and the registry.

## Core rule

Do not collapse all transformations into one `normalizeName()` call.

The package keeps these meanings separate:

```text
normalize     technical Unicode/case/whitespace normalization
transliterate script conversion
fold          comparison-oriented technical folding
variants      candidate linguistic/name-family relations
```

Each operation returns a `ProcessingResult`, but the underlying service contracts remain separate because the operations do not have identical semantics.

## Framework boundary

Reusable code has no Laravel dependency. Laravel/MyTree adapters may bind the public contracts for Source Acquisition, Search, Engine or other application use cases without moving framework concerns into this package.

The package does not own the consuming application's persistence model. All processor results remain derived data: Source Acquisition may display or use them as assistance, but must not persist them into `Source`, `Mention`, `Claim` or `ClaimRevision` merely because they were accepted as useful. Search may use normalization/folding/transliteration/variants as rebuildable comparison data, and a future derived-representation store may retain them with lineage to immutable source inputs.

If the acquired historical Source itself contains more than one linguistic form, recording those forms as direct source evidence is handled by Source Acquisition independently from this package.

## Resource adapters

Profiles and variant datasets are serialized adapters behind repository contracts. Readers validate supported schema IDs and fail explicitly for incompatible resources.

This keeps serialized data versioning separate from the domain service interfaces and allows storage to be replaced later.

## Determinism

Processing algorithms do not read clocks, randomness, global configuration, or network state. Semantic output depends on explicit input, selected versioned resources, implementation behavior, and the ICU version recorded in result metadata where relevant.

## Source preservation

Normalization, transliteration, folding, and variants are derived representations. They must not overwrite the original spelling acquired from a historical document or external provider.

The package is currently name-focused. It does not define a generic lexical translation/dictionary contract for occupations, social-position terms or arbitrary Source fields. Broadening that responsibility requires a separate explicit design.


## Persistence

This package does not persist MyTree `Mention`, `Claim`, source-supplied linguistic representations, derived-representation context or Search projections. Persistence belongs to the consuming application. `ProcessingResult` carries profile and implementation metadata so a derived consumer can retain processor provenance and reproducible lineage where needed.

Calling a processing operation is never itself an Acquisition mutation. Normalize/fold/transliterate/variant output must not be silently promoted into Claim semantic state. A source-supplied alternative form belongs to Claim history only when the historical Source itself explicitly contains that form; the processor neither establishes nor changes that fact.

## Morphology

No `MorphologicalAnalyzerInterface` is defined yet. Its contract should be designed only after deciding whether analysis needs a token, sentence context, historical-language models, multiple lemmas, grammatical features, or an external service.
