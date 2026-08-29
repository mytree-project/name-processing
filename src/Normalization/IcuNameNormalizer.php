<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Normalization;

use MyTree\NameProcessing\Contracts\NameNormalizerInterface;
use MyTree\NameProcessing\Contracts\ProfileRepositoryInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\OutputValue;
use MyTree\NameProcessing\Domain\ProcessingResult;
use MyTree\NameProcessing\Exception\NameProcessingException;
use Normalizer;

final class IcuNameNormalizer implements NameNormalizerInterface
{
    public function __construct(private readonly ProfileRepositoryInterface $profiles)
    {
    }

    public function normalize(NameInput $input, string $profileId = 'default'): ProcessingResult
    {
        $profile = $this->profiles->get('normalize', $profileId);
        $value = $this->normalizeValue($input->value, $profile->config);

        return new ProcessingResult(
            operation: 'normalize',
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
                'unicode_form' => $profile->config['unicode_form'] ?? 'NFC',
                'icu_version' => defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : null,
            ],
        );
    }

    /** @param array<string, mixed> $config */
    private function normalizeValue(string $value, array $config): string
    {
        if (!class_exists(Normalizer::class)) {
            throw new NameProcessingException('PHP extension ext-intl is required.', 'missing_extension');
        }

        $formName = strtoupper((string) ($config['unicode_form'] ?? 'NFC'));
        $form = match ($formName) {
            'NFC' => Normalizer::FORM_C,
            'NFD' => Normalizer::FORM_D,
            'NFKC' => Normalizer::FORM_KC,
            'NFKD' => Normalizer::FORM_KD,
            default => throw new NameProcessingException("Unsupported Unicode form: {$formName}", 'invalid_profile'),
        };

        $normalized = Normalizer::normalize($value, $form);
        if ($normalized === false) {
            throw new NameProcessingException('ICU Unicode normalization failed.', 'icu_error');
        }

        if (($config['collapse_whitespace'] ?? false) === true) {
            $collapsed = preg_replace('/[\p{Z}\s]+/u', ' ', $normalized);
            if ($collapsed === null) {
                throw new NameProcessingException('Whitespace normalization failed.', 'unicode_error');
            }
            $normalized = $collapsed;
        }

        if (($config['trim'] ?? false) === true) {
            $normalized = trim($normalized);
        }

        if (($config['case_fold'] ?? false) === true) {
            if (!function_exists('mb_convert_case')) {
                throw new NameProcessingException('PHP extension ext-mbstring is required.', 'missing_extension');
            }
            $normalized = mb_convert_case($normalized, MB_CASE_FOLD, 'UTF-8');
        }

        return $normalized;
    }
}
