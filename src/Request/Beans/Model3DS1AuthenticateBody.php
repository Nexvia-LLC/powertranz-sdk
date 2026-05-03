<?php

declare(strict_types=1);

namespace PowerTranz\Request\Beans;

/**
 * 3-D Secure 1.0 authenticate POST body (ACS redirect flow).
 */
final class Model3DS1AuthenticateBody
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly ?string $paReq = null,
        public readonly ?string $md = null,
        public readonly ?string $termUrl = null,
        public readonly ?string $acsUrl = null,
        public readonly ?string $xid = null,
        public readonly array $raw = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            paReq: isset($data['PaReq']) ? (string) $data['PaReq'] : null,
            md: isset($data['MD']) ? (string) $data['MD'] : null,
            termUrl: isset($data['TermUrl']) ? (string) $data['TermUrl'] : null,
            acsUrl: isset($data['AcsUrl']) ? (string) $data['AcsUrl'] : null,
            xid: isset($data['Xid']) ? (string) $data['Xid'] : null,
            raw: $data,
        );
    }

    /**
     * Typical HTML form fields for posting to the ACS.
     *
     * @return array<string, string>
     */
    public function toFormFields(): array
    {
        $fields = [];
        if ($this->paReq !== null) {
            $fields['PaReq'] = $this->paReq;
        }
        if ($this->md !== null) {
            $fields['MD'] = $this->md;
        }
        if ($this->termUrl !== null) {
            $fields['TermUrl'] = $this->termUrl;
        }

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'PaReq' => $this->paReq,
            'MD' => $this->md,
            'TermUrl' => $this->termUrl,
            'AcsUrl' => $this->acsUrl,
            'Xid' => $this->xid,
        ], static fn(mixed $v): bool => $v !== null && $v !== '');
    }
}
