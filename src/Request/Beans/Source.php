<?php

declare(strict_types=1);

namespace PowerTranz\Request\Beans;

use PowerTranz\Exceptions\ValidationException;

/**
 * Card payment source (PAN entry or {@see Source::fromPanToken token-only}).
 *
 * @note Do NOT populate this for HPP integrations.
 *       Card data is collected by PowerTranz on the hosted page.
 */
class Source
{
    private ?string $cardPan          = null;
    private ?string $cardCvv          = null;
    private ?string $cardExpiration   = null;
    private ?string $cardholderName   = null;
    private ?string $cardTrack1       = null;
    private ?string $cardTrack2       = null;
    private ?string $panToken         = null;

    public function __construct(
        string $cardPan,
        string $cardCvv,
        string $cardExpiration,
        ?string $cardholderName = null,
    ) {
        $this->cardPan        = $cardPan;
        $this->cardCvv        = $cardCvv;
        $this->cardExpiration = $cardExpiration;
        $this->cardholderName = $cardholderName;
        $this->validate();
    }

    // Named constructor for token-based payments
    public static function fromPanToken(string $panToken): self
    {
        $instance           = new self('0000000000000000', '000', '0101');
        $instance->panToken = $panToken;
        $instance->cardPan  = null;
        $instance->cardCvv  = null;
        return $instance;
    }

    public function getCardPan(): ?string        { return $this->cardPan; }
    public function getCardCvv(): ?string        { return $this->cardCvv; }
    public function getCardExpiration(): ?string { return $this->cardExpiration; }
    public function getCardholderName(): ?string { return $this->cardholderName; }
    public function getPanToken(): ?string       { return $this->panToken; }

    public function setCardholderName(string $name): void { $this->cardholderName = $name; }
    public function setCardTrack1(string $track): void    { $this->cardTrack1 = $track; }
    public function setCardTrack2(string $track): void    { $this->cardTrack2 = $track; }

    public function toArray(): array
    {
        $data = [];

        if ($this->panToken !== null) {
            $data['PanToken'] = $this->panToken;
            return $data;
        }

        if ($this->cardPan !== null)        $data['CardPan']        = $this->cardPan;
        if ($this->cardCvv !== null)        $data['CardCvv']        = $this->cardCvv;
        if ($this->cardExpiration !== null) $data['CardExpiration']  = $this->cardExpiration;
        if ($this->cardholderName !== null) $data['CardholderName']  = $this->cardholderName;
        if ($this->cardTrack1 !== null)     $data['CardTrack1']     = $this->cardTrack1;
        if ($this->cardTrack2 !== null)     $data['CardTrack2']     = $this->cardTrack2;

        return $data;
    }

    private function validate(): void
    {
        if (!preg_match('/^\d{12,19}$/', $this->cardPan)) {
            throw new ValidationException('CardPan must be 12–19 digits.');
        }
        if (!preg_match('/^\d{3,4}$/', $this->cardCvv)) {
            throw new ValidationException('CardCvv must be 3 or 4 digits.');
        }
        if (!preg_match('/^\d{4}$/', $this->cardExpiration)) {
            throw new ValidationException('CardExpiration must be YYMM format (e.g. 2310).');
        }
    }
}
