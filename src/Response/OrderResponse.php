<?php

declare(strict_types=1);

namespace PowerTranz\Response;

/**
 * Single row from {@see \PowerTranz\PowerTranzClient::searchTransactions}.
 *
 * Wraps {@see TransactionResponse} so search-specific typing stays explicit.
 */
final class OrderResponse
{
    public function __construct(
        public readonly TransactionResponse $transaction,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(TransactionResponse::fromArray($data));
    }
}
