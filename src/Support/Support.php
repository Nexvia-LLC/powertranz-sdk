<?php

declare(strict_types=1);

namespace PowerTranz\Support;

/**
 * ISO 4217 numeric currency codes used by PowerTranz.
 * Includes all Caribbean-relevant currencies.
 */
final class CurrencyCode
{
    public const USD = '840'; // US Dollar
    public const EUR = '978'; // Euro
    public const GBP = '826'; // British Pound
    public const CAD = '124'; // Canadian Dollar
    public const JMD = '388'; // Jamaican Dollar
    public const TTD = '780'; // Trinidad and Tobago Dollar
    public const BBD = '052'; // Barbadian Dollar
    public const BSD = '044'; // Bahamian Dollar
    public const KYD = '136'; // Cayman Islands Dollar
    public const XCD = '951'; // Eastern Caribbean Dollar
    public const HTG = '332'; // Haitian Gourde
    public const DOP = '214'; // Dominican Peso
    public const AWG = '533'; // Aruban Florin
    public const ANG = '532'; // Netherlands Antillean Guilder
    public const BZD = '084'; // Belize Dollar
    public const GYD = '328'; // Guyanese Dollar
    public const SRD = '968'; // Surinamese Dollar

    private static array $labels = [
        self::USD => 'US Dollar',        self::EUR => 'Euro',
        self::GBP => 'British Pound',    self::CAD => 'Canadian Dollar',
        self::JMD => 'Jamaican Dollar',  self::TTD => 'Trinidad & Tobago Dollar',
        self::BBD => 'Barbadian Dollar', self::BSD => 'Bahamian Dollar',
        self::KYD => 'Cayman Islands Dollar', self::XCD => 'Eastern Caribbean Dollar',
        self::HTG => 'Haitian Gourde',   self::DOP => 'Dominican Peso',
        self::AWG => 'Aruban Florin',    self::ANG => 'Netherlands Antillean Guilder',
        self::BZD => 'Belize Dollar',    self::GYD => 'Guyanese Dollar',
        self::SRD => 'Surinamese Dollar',
    ];

    public static function label(string $code): string  { return self::$labels[$code] ?? "Unknown ({$code})"; }
    public static function isValid(string $code): bool  { return isset(self::$labels[$code]); }
}

/**
 * SpiToken TTL helper — tokens expire after exactly 5 minutes.
 */
final class SpiTokenHelper
{
    public const TTL_SECONDS = 300;

    public static function isValid(\DateTimeImmutable $issuedAt, ?\DateTimeImmutable $now = null): bool
    {
        $now ??= new \DateTimeImmutable();
        return $now < $issuedAt->modify('+' . self::TTL_SECONDS . ' seconds');
    }

    public static function secondsRemaining(\DateTimeImmutable $issuedAt, ?\DateTimeImmutable $now = null): int
    {
        $now ??= new \DateTimeImmutable();
        return max(0, $issuedAt->modify('+' . self::TTL_SECONDS . ' seconds')->getTimestamp() - $now->getTimestamp());
    }
}

/**
 * Renders the PowerTranz RedirectData into a cardholder-browser iFrame.
 */
final class RedirectDataRenderer
{
    public static function render(
        \PowerTranz\Response\TransactionResponse $response,
        string $width = '100%',
        int $height = 500,
        string $id = 'powertranz-iframe',
    ): string {
        if (!$response->hasRedirectData()) {
            throw new \RuntimeException('No RedirectData in response. Ensure IsoResponseCode = SP4 before rendering.');
        }

        $srcdoc = htmlspecialchars($response->redirectData, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <iframe
            id="{$id}"
            srcdoc="{$srcdoc}"
            frameborder="0"
            width="{$width}"
            height="{$height}"
            style="border:none;overflow:hidden;"
            sandbox="allow-scripts allow-forms allow-same-origin allow-popups"
        ></iframe>
        HTML;
    }

    public static function merchantResponseRedirectScript(string $resultPageUrl): string
    {
        $url = htmlspecialchars($resultPageUrl, ENT_QUOTES, 'UTF-8');
        return "<script>\nwindow.onload = function() { window.parent.location = '{$url}'; };\n</script>";
    }
}
