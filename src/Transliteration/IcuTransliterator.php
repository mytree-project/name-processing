<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Transliteration;

use MyTree\NameProcessing\Contracts\ProfileRepositoryInterface;
use MyTree\NameProcessing\Contracts\TransliteratorInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\OutputValue;
use MyTree\NameProcessing\Domain\ProcessingResult;
use MyTree\NameProcessing\Exception\NameProcessingException;
use Normalizer;
use Transliterator;

final class IcuTransliterator implements TransliteratorInterface
{
    public function __construct(private readonly ProfileRepositoryInterface $profiles)
    {
    }

    public function transliterate(NameInput $input, string $profileId = 'cyrillic-latin'): ProcessingResult
    {
        $profile = $this->profiles->get('transliterate', $profileId);
        $icuId = (string) ($profile->config['icu_id'] ?? '');
        if ($icuId === '') {
            throw new NameProcessingException('Transliteration profile has no ICU ID.', 'invalid_profile');
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
            throw new NameProcessingException('ICU transliteration failed.', 'icu_error');
        }

        $value = $this->normalizeOutput($value, $profile->config['normalize_output'] ?? null);

        return new ProcessingResult(
            operation: 'transliterate',
            input: $input,
            profile: $profile,
            results: [new OutputValue(
                value: $value,
                language: $input->language,
                script: is_string($profile->config['output_script'] ?? null)
                    ? $profile->config['output_script']
                    : $input->script,
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
            throw new NameProcessingException('Unsupported transliteration output normalization form.', 'invalid_profile');
        }

        $normalized = Normalizer::normalize($value, Normalizer::FORM_C);
        if ($normalized === false) {
            throw new NameProcessingException('ICU Unicode normalization failed.', 'icu_error');
        }

        return $normalized;
    }
}
