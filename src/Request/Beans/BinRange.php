<?php

declare(strict_types=1);

namespace PowerTranz\Request\Beans;

/**
 * BIN range metadata for integrations that expose range lookups.
 *
 * Field names follow typical gateway JSON shapes; unknown keys are preserved via {@see self::raw}.
 */
final class BinRange
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly ?string $rangeStart = null,
        public readonly ?string $rangeEnd = null,
        public readonly ?string $cardScheme = null,
        public readonly ?string $issuerCountry = null,
        public readonly ?string $productType = null,
        public readonly ?string $detailText = null,
        public readonly array $raw = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            rangeStart: isset($data['RangeStart']) ? (string) $data['RangeStart'] : null,
            rangeEnd: isset($data['RangeEnd']) ? (string) $data['RangeEnd'] : null,
            cardScheme: isset($data['CardScheme']) ? (string) $data['CardScheme'] : null,
            issuerCountry: isset($data['IssuerCountry']) ? (string) $data['IssuerCountry'] : null,
            productType: isset($data['ProductType']) ? (string) $data['ProductType'] : null,
            detailText: isset($data['DetailText']) ? (string) $data['DetailText'] : null,
            raw: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'RangeStart' => $this->rangeStart,
            'RangeEnd' => $this->rangeEnd,
            'CardScheme' => $this->cardScheme,
            'IssuerCountry' => $this->issuerCountry,
            'ProductType' => $this->productType,
            'DetailText' => $this->detailText,
        ], static fn(mixed $v): bool => $v !== null && $v !== '');
    }
}
