<?php

declare(strict_types=1);

namespace MyTree\NameProcessing\Variants;

use MyTree\NameProcessing\Contracts\NameNormalizerInterface;
use MyTree\NameProcessing\Contracts\NameVariantResolverInterface;
use MyTree\NameProcessing\Contracts\ProfileRepositoryInterface;
use MyTree\NameProcessing\Contracts\VariantRepositoryInterface;
use MyTree\NameProcessing\Domain\NameInput;
use MyTree\NameProcessing\Domain\NameType;
use MyTree\NameProcessing\Domain\OutputValue;
use MyTree\NameProcessing\Domain\ProcessingResult;
use MyTree\NameProcessing\Domain\VariantEntry;
use MyTree\NameProcessing\Exception\NameProcessingException;

final class NameVariantResolver implements NameVariantResolverInterface
{
    public function __construct(
        private readonly ProfileRepositoryInterface $profiles,
        private readonly VariantRepositoryInterface $variants,
        private readonly NameNormalizerInterface $normalizer,
    ) {
    }

    public function resolve(NameInput $input, string $profileId = 'genealogy-pl-ru'): ProcessingResult
    {
        $profile = $this->profiles->get('variants', $profileId);
        $datasetId = (string) ($profile->config['dataset'] ?? '');
        $normalizerProfile = (string) ($profile->config['normalizer_profile'] ?? 'default');
        $relation = (string) ($profile->config['relation'] ?? 'language_equivalent_candidate');

        if ($datasetId === '') {
            throw new NameProcessingException('Variant profile has no dataset.', 'invalid_profile');
        }

        $dataset = $this->variants->load($datasetId);
        $effectiveType = $input->type ?? NameType::GivenName;
        $needle = $this->normalizer->normalize(
            new NameInput($input->value, $input->language, $input->script, $effectiveType),
            $normalizerProfile,
        )->firstValue();

        $output = [];
        $matchedGroups = [];

        foreach ($dataset->groups as $group) {
            $matched = false;
            foreach ($group->variants as $variant) {
                if ($variant->type !== $effectiveType) {
                    continue;
                }

                $candidate = $this->normalizer->normalize(
                    new NameInput($variant->value, $variant->language, $variant->script, $variant->type),
                    $normalizerProfile,
                )->firstValue();

                if ($candidate === $needle && $this->hintsCompatible($input, $variant)) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            $matchedGroups[] = $group->id;
            foreach ($group->variants as $variant) {
                if ($variant->type !== $effectiveType) {
                    continue;
                }

                if ($variant->value === $input->value
                    && ($input->language === null || $input->language === $variant->language)
                    && ($input->script === null || $input->script === $variant->script)) {
                    continue;
                }

                $output[] = new OutputValue(
                    value: $variant->value,
                    language: $variant->language,
                    script: $variant->script,
                    type: $variant->type,
                    relation: $relation,
                    metadata: array_merge($variant->metadata, [
                        'group_id' => $group->id,
                    ]),
                );
            }
        }

        return new ProcessingResult(
            operation: 'variants',
            input: new NameInput($input->value, $input->language, $input->script, $effectiveType),
            profile: $profile,
            results: $this->deduplicate($output),
            metadata: [
                'implementation' => 'dataset-resolver',
                'dataset_id' => $dataset->id,
                'dataset_version' => $dataset->version,
                'matched_groups' => array_values(array_unique($matchedGroups)),
            ],
        );
    }

    private function hintsCompatible(NameInput $input, VariantEntry $variant): bool
    {
        if ($input->language !== null && $variant->language !== null && $input->language !== $variant->language) {
            return false;
        }

        if ($input->script !== null && $variant->script !== null && $input->script !== $variant->script) {
            return false;
        }

        return true;
    }

    /**
     * @param list<OutputValue> $values
     * @return list<OutputValue>
     */
    private function deduplicate(array $values): array
    {
        $seen = [];
        $out = [];
        foreach ($values as $value) {
            $key = implode('|', [
                $value->value,
                $value->language ?? '',
                $value->script ?? '',
                $value->type->value ?? '',
            ]);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $value;
        }

        return $out;
    }
}
