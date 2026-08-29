<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Folding;

use MyTree\NameProcessing\Contracts\NameFolderInterface;
use MyTree\NameProcessing\Contracts\ProfileRepositoryInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\OutputValue;
use MyTree\NameProcessing\Domain\ProcessingResult;
use MyTree\NameProcessing\Exception\NameProcessingException;
use Normalizer;
use Transliterator;

final class IcuNameFolder implements NameFolderInterface
{
    public function __construct(private readonly ProfileRepositoryInterface $profiles)
    {
    }

    public function fold(NameInput $input, string $profileId = 'latin-search'): ProcessingResult
    {
        $profile = $this->profiles->get('fold', $profileId);
        $icuId = (string) ($profile->config['icu_id'] ?? '');
        if ($icuId === '') {
            throw new NameProcessingException('Fold profile has no ICU ID.', 'invalid_profile');
        }

        if (!class_exists(Transliterator::class)) {
            throw new NameProcessingException('PHP extension ext-intl is required.', 'missing_extension');
        }

        $transliterator = Transliterator::create($icuId);
        if ($transliterator === null) {
            throw new NameProcessingException("Unable to create ICU transliterator: {$icuId}", 'icu_error');
        }

        $value = $transliterator->transliterate($input->value);
        if ($value === false) {
            throw new NameProcessingException('ICU folding failed.', 'icu_error');
        }

        if (($profile->config['collapse_whitespace'] ?? false) === true) {
            $collapsed = preg_replace('/[\p{Z}\s]+/u', ' ', $value);
            if ($collapsed === null) {
                throw new NameProcessingException('Whitespace folding failed.', 'unicode_error');
            }
            $value = $collapsed;
        }

        if (($profile->config['trim'] ?? false) === true) {
            $value = trim($value);
        }

        if (($profile->config['case_fold'] ?? false) === true) {
            if (!function_exists('mb_convert_case')) {
                throw new NameProcessingException('PHP extension ext-mbstring is required.', 'missing_extension');
            }
            $value = mb_convert_case($value, MB_CASE_FOLD, 'UTF-8');
        }

        $value = $this->normalizeOutput($value, $profile->config['normalize_output'] ?? null);

        return new ProcessingResult(
            operation: 'fold',
            input: $input,
            profile: $profile,
            results: [new OutputValue(
                value: $value,
                language: $input->language,
                script: $input->script,
                type: $input->type,
            )],
            metadata: [
                'implementation' => 'icu',
                'icu_id' => $icuId,
                'icu_version' => defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : null,
            ],
        );
    }

    private function normalizeOutput(string $value, mixed $form): string
    {
        if ($form === null) {
            return $value;
        }

        if ($form !== 'NFC') {
            throw new NameProcessingException('Unsupported fold output normalization form.', 'invalid_profile');
        }

        $normalized = Normalizer::normalize($value, Normalizer::FORM_C);
        if ($normalized === false) {
            throw new NameProcessingException('ICU Unicode normalization failed.', 'icu_error');
        }

        return $normalized;
    }
}
