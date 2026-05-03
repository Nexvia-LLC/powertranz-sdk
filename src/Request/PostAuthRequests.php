<?php

declare(strict_types=1);

namespace PowerTranz\Request;

use PowerTranz\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * Capture — settles a previously authorized transaction.
 */
class CaptureRequest
{
    private string  $transactionIdentifier;
    private float   $totalAmount;
    private string  $currencyCode;
    private ?string $externalIdentifier      = null;
    private ?string $externalBatchIdentifier = null;
    private ?float  $tipAmount               = null;
    private ?string $terminalId              = null;
    private ?string $terminalCode            = null;

    public function __construct(
        string $transactionIdentifier,
        float $totalAmount,
        string $currencyCode
    ) {
        if (empty(trim($transactionIdentifier))) throw new ValidationException('TransactionIdentifier is required for Capture.');
        if ($totalAmount <= 0) throw new ValidationException('Capture TotalAmount must be > 0.');
        if (!preg_match('/^\d{3}$/', $currencyCode)) throw new ValidationException('Invalid CurrencyCode.');

        $this->transactionIdentifier = $transactionIdentifier;
        $this->totalAmount           = $totalAmount;
        $this->currencyCode          = $currencyCode;
    }

    public function setExternalIdentifier(string $v): static      { $this->externalIdentifier = $v; return $this; }
    public function setExternalBatchIdentifier(string $v): static { $this->externalBatchIdentifier = $v; return $this; }
    public function setTipAmount(float $v): static                { $this->tipAmount = $v; return $this; }
    public function setTerminalId(string $v): static              { $this->terminalId = $v; return $this; }
    public function setTerminalCode(string $v): static            { $this->terminalCode = $v; return $this; }

    public function toArray(): array
    {
        $data = [
            'TransactionIdentifier' => $this->transactionIdentifier,
            'TotalAmount'           => $this->totalAmount,
            'CurrencyCode'          => $this->currencyCode,
        ];
        if ($this->tipAmount !== null)               $data['TipAmount']               = $this->tipAmount;
        if ($this->externalIdentifier !== null)      $data['ExternalIdentifier']      = $this->externalIdentifier;
        if ($this->externalBatchIdentifier !== null) $data['ExternalBatchIdentifier'] = $this->externalBatchIdentifier;
        if ($this->terminalId !== null)              $data['TerminalId']              = $this->terminalId;
        if ($this->terminalCode !== null)            $data['TerminalCode']            = $this->terminalCode;
        return $data;
    }
}

/**
 * Refund — reverses a settled transaction (full or partial; amount per agreement with the gateway).
 */
class RefundRequest
{
    private string  $transactionIdentifier;
    private float   $totalAmount;
    private string  $currencyCode;
    private ?string $externalIdentifier  = null;
    private ?string $orderIdentifier     = null;
    private ?string $terminalId          = null;
    private mixed   $customData          = null;

    public function __construct(
        string $transactionIdentifier,
        float $totalAmount,
        string $currencyCode
    ) {
        if (empty(trim($transactionIdentifier))) throw new ValidationException('TransactionIdentifier is required for Refund.');
        if ($totalAmount <= 0) throw new ValidationException('Refund TotalAmount must be > 0.');
        if (!preg_match('/^\d{3}$/', $currencyCode)) throw new ValidationException('Invalid CurrencyCode.');

        $this->transactionIdentifier = $transactionIdentifier;
        $this->totalAmount           = $totalAmount;
        $this->currencyCode          = $currencyCode;
    }

    public function setExternalIdentifier(string $v): static { $this->externalIdentifier = $v; return $this; }
    public function setOrderIdentifier(string $v): static    { $this->orderIdentifier = $v; return $this; }
    public function setTerminalId(string $v): static         { $this->terminalId = $v; return $this; }
    public function setCustomData(mixed $v): static          { $this->customData = $v; return $this; }

    public function toArray(): array
    {
        $data = [
            'TransactionIdentifier' => $this->transactionIdentifier,
            'TotalAmount'           => $this->totalAmount,
            'CurrencyCode'          => $this->currencyCode,
        ];
        if ($this->externalIdentifier !== null) $data['ExternalIdentifier'] = $this->externalIdentifier;
        if ($this->orderIdentifier !== null)    $data['OrderIdentifier']    = $this->orderIdentifier;
        if ($this->terminalId !== null)         $data['TerminalId']         = $this->terminalId;
        if ($this->customData !== null)         $data['CustomData']         = $this->customData;
        return $data;
    }
}

/**
 * Void — cancels an authorization before capture.
 */
class VoidRequest
{
    private string  $transactionIdentifier;
    private ?string $externalIdentifier = null;
    private ?string $terminalId         = null;
    private mixed   $customData         = null;

    public function __construct(string $transactionIdentifier)
    {
        if (empty(trim($transactionIdentifier))) throw new ValidationException('TransactionIdentifier is required for Void.');
        $this->transactionIdentifier = $transactionIdentifier;
    }

    public function setExternalIdentifier(string $v): static { $this->externalIdentifier = $v; return $this; }
    public function setTerminalId(string $v): static         { $this->terminalId = $v; return $this; }
    public function setCustomData(mixed $v): static          { $this->customData = $v; return $this; }

    public function toArray(): array
    {
        $data = ['TransactionIdentifier' => $this->transactionIdentifier];
        if ($this->externalIdentifier !== null) $data['ExternalIdentifier'] = $this->externalIdentifier;
        if ($this->terminalId !== null)         $data['TerminalId']         = $this->terminalId;
        if ($this->customData !== null)         $data['CustomData']         = $this->customData;
        return $data;
    }
}

/**
 * Transaction search — builds query params for GET `/api/transactions/search`.
 */
class TransactionSearchRequest
{
    private ?string $orderIdentifier    = null;
    private ?string $externalIdentifier = null;
    private ?string $fromDate           = null;
    private ?string $toDate             = null;
    private ?string $cardSuffix         = null;
    private ?int    $page               = null;
    private ?int    $pageSize           = null;

    public function setOrderIdentifier(string $v): static    { $this->orderIdentifier = $v; return $this; }
    public function setExternalIdentifier(string $v): static { $this->externalIdentifier = $v; return $this; }
    public function setFromDate(string $v): static           { $this->fromDate = $v; return $this; }
    public function setToDate(string $v): static             { $this->toDate = $v; return $this; }
    public function setCardSuffix(string $v): static         { $this->cardSuffix = $v; return $this; }
    public function setPage(int $v): static                  { $this->page = $v; return $this; }
    public function setPageSize(int $v): static              { $this->pageSize = $v; return $this; }

    public function toQueryParams(): array
    {
        $params = [];
        if ($this->orderIdentifier !== null)    $params['orderIdentifier']    = $this->orderIdentifier;
        if ($this->externalIdentifier !== null) $params['externalIdentifier'] = $this->externalIdentifier;
        if ($this->fromDate !== null)           $params['fromDate']           = $this->fromDate;
        if ($this->toDate !== null)             $params['toDate']             = $this->toDate;
        if ($this->cardSuffix !== null)         $params['cardSuffix']         = $this->cardSuffix;
        if ($this->page !== null)               $params['page']               = $this->page;
        if ($this->pageSize !== null)           $params['pageSize']           = $this->pageSize;
        return $params;
    }
}
