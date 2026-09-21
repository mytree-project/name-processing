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

The package does not own the consuming application's decision to persist a processor result. In particular, Acquisition may explicitly accept a processor-produced transliteration/name variant as a linked linguistic representation with provenance, while Search may use normalization/folding only as rebuildable comparison data.

## Resource adapters

Profiles and variant datasets are serialized adapters behind repository contracts. Readers validate supported schema IDs and fail explicitly for incompatible resources.

This keeps serialized data versioning separate from the domain service interfaces and allows storage to be replaced later.

## Determinism

Processing algorithms do not read clocks, randomness, global configuration, or network state. Semantic output depends on explicit input, selected versioned resources, implementation behavior, and the ICU version recorded in result metadata where relevant.

## Source preservation

Normalization, transliteration, folding, and variants are derived representations. They must not overwrite the original spelling acquired from a historical document or external provider.

The package is currently name-focused. It does not define a generic lexical translation/dictionary contract for occupations, social-position terms or arbitrary Source fields. Broadening that responsibility requires a separate explicit design.


## Persistence

This package does not persist MyTree `Mention`, `Claim`, linked linguistic representations or Search projections. Persistence belongs to the consuming application. `ProcessingResult` carries profile and implementation metadata needed by a persistence adapter to retain processor provenance when a result is explicitly accepted.

Calling a processing operation is never itself an Acquisition mutation. Technical normalize/fold output used only for comparison should remain rebuildable derived data rather than being silently promoted into Claim semantic state.

## Morphology

No `MorphologicalAnalyzerInterface` is defined yet. Its contract should be designed only after deciding whether analysis needs a token, sentence context, historical-language models, multiple lemmas, grammatical features, or an external service.
