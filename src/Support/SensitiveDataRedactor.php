<?php

declare(strict_types=1);

namespace PowerTranz\Support;

/**
 * Redacts PAN-like data and sensitive keys before logging request/response payloads.
 */
final class SensitiveDataRedactor
{
    /** @var list<string> */
    private const KEY_LOWER_EXACT = [
        'cardpan',
        'cardcvv',
        'pantoken',
        'cardexpiration',
        'cardtrack1',
        'cardtrack2',
    ];

    private const MASK = '[REDACTED]';

    /**
     * Deep-copy an array with sensitive scalar values replaced.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function redactArray(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            $key = is_string($k) ? $k : (string) $k;
            if ($this->isSensitiveKey($key)) {
                $out[$key] = self::MASK;
                continue;
            }
            $out[$key] = $this->redactValue($v);
        }

        return $out;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function redactValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->redactArray($value);
        }
        if (is_string($value)) {
            return $this->redactString($value);
        }

        return $value;
    }

    private function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);
        if (in_array($lower, self::KEY_LOWER_EXACT, true)) {
            return true;
        }

        return str_contains($lower, 'password');
    }

    /**
     * Mask sequences of 13–19 digits (PAN-shaped). Avoid masking small integers (amounts, ISO codes).
     */
    public function redactString(string $s): string
    {
        return preg_replace('/\b\d{13,19}\b/', self::MASK, $s) ?? $s;
    }

    /**
     * Redact HTTP headers for logging (never log raw credentials).
     *
     * @param array<string, string|string[]> $headers
     * @return array<string, string|string[]>
     */
    public function redactHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $name => $value) {
            $n = (string) $name;
            if ($this->isSensitiveHeader($n)) {
                $out[$n] = self::MASK;
                continue;
            }
            if (is_array($value)) {
                $out[$n] = array_map(fn(string $v): string => $this->redactString($v), $value);
            } else {
                $out[$n] = $this->redactString((string) $value);
            }
        }

        return $out;
    }

    private function isSensitiveHeader(string $name): bool
    {
        $lower = strtolower($name);

        return str_contains($lower, 'powertranz-password')
            || str_contains($lower, 'powertranz-powertranzpassword')
            || str_contains($lower, 'powertranz-powertranzid')
            || str_contains($lower, 'powertranz-gatewaykey');
    }
}
