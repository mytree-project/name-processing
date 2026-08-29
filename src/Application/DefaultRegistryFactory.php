<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Application;

use MyTree\NameProcessing\Folding\IcuNameFolder;
use MyTree\NameProcessing\Normalization\IcuNameNormalizer;
use MyTree\NameProcessing\Profile\JsonProfileRepository;
use MyTree\NameProcessing\Transliteration\IcuTransliterator;
use MyTree\NameProcessing\Variants\JsonVariantRepository;
use MyTree\NameProcessing\Variants\NameVariantResolver;

final class DefaultRegistryFactory
{
    public static function create(string $packageRoot): OperationRegistry
    {
        $profiles = new JsonProfileRepository($packageRoot . '/resources/profiles');
        $variants = new JsonVariantRepository($packageRoot . '/resources/variants');

        $normalizer = new IcuNameNormalizer($profiles);
        $transliterator = new IcuTransliterator($profiles);
        $folder = new IcuNameFolder($profiles);
        $variantResolver = new NameVariantResolver($profiles, $variants, $normalizer);

        return new OperationRegistry([
            new NormalizeOperation($normalizer),
            new TransliterateOperation($transliterator),
            new FoldOperation($folder),
            new VariantsOperation($variantResolver),
        ]);
    }
}
