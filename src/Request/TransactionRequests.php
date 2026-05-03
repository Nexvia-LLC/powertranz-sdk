<?php

declare(strict_types=1);

namespace PowerTranz\Request;

/**
 * Sale — authorization with automatic capture (gateway/product rules apply).
 * Use with `PowerTranzClient::sale()` → `/api/spi/sale`, or `standardSale()` → `/api/sale`.
 *
 * @example
 * $request = new SaleRequest(25.00, '840');
 * $request->setThreeDSecure(true)
 *         ->setSource(new Source('4012...', '323', '2310', 'John Doe'))
 *         ->setEmail('john@example.com');
 */
class SaleRequest extends BaseTransactionRequest {}

/**
 * Authorization only — funds held until Capture. Supports optional tokenization flags beyond Sale.
 * Use with `authorize()` → `/api/spi/auth`, or `standardAuthorize()` → `/api/auth`.
 */
class AuthRequest extends BaseTransactionRequest {}

/**
 * Non-financial — 3DS authentication or tokenization only (no charge).
 * Use with `riskManagement()` → `/api/spi/riskmgmt`.
 */
class NonfinancialRequest extends BaseTransactionRequest {}
