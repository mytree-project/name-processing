# Changelog

## 0.1.0 - MVP

- framework-independent PHP 8.2+ package with PSR-4 autoloading;
- deterministic `normalize`, `transliterate`, `fold`, and `variants` operations;
- one JSON CLI entry point: `bin/mytree-name`;
- versioned processing profiles and variant dataset schemas with explicit validation;
- ICU implementation metadata for reproducibility;
- PHPUnit unit/integration tests;
- PHPStan static analysis and PHP CS Fixer formatting checks;
- GitHub Actions quality gate on PHP 8.2 and 8.4;
- no Laravel dependency and no morphological analysis in the MVP.
