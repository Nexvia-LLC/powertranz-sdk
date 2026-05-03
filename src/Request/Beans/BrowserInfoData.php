<?php

declare(strict_types=1);

namespace PowerTranz\Request\Beans;

/**
 * Browser/device information for 3-D Secure 2 fingerprinting.
 * Populate from the cardholder's browser (e.g. `$_SERVER`, client-collected fields).
 */
class BrowserInfoData
{
    private ?string $acceptHeader      = null;
    private ?string $language          = null;
    private ?string $screenHeight      = null;
    private ?string $screenWidth       = null;
    private ?string $timeZone          = null;
    private ?string $userAgent         = null;
    private ?string $ip                = null;
    private ?bool   $javaEnabled       = null;
    private ?bool   $javascriptEnabled = null;
    private ?string $colorDepth        = null;

    public function setAcceptHeader(string $v): void      { $this->acceptHeader = $v; }
    public function setLanguage(string $v): void          { $this->language = $v; }
    public function setScreenHeight(string $v): void      { $this->screenHeight = $v; }
    public function setScreenWidth(string $v): void       { $this->screenWidth = $v; }
    public function setTimeZone(string $v): void          { $this->timeZone = $v; }
    public function setUserAgent(string $v): void         { $this->userAgent = $v; }
    public function setIp(string $v): void                { $this->ip = $v; }
    public function setJavaEnabled(bool $v): void         { $this->javaEnabled = $v; }
    public function setJavascriptEnabled(bool $v): void   { $this->javascriptEnabled = $v; }
    public function setColorDepth(string $v): void        { $this->colorDepth = $v; }

    public function toArray(): array
    {
        $data = [];
        if ($this->acceptHeader !== null)      $data['AcceptHeader']      = $this->acceptHeader;
        if ($this->language !== null)          $data['Language']          = $this->language;
        if ($this->screenHeight !== null)      $data['ScreenHeight']      = $this->screenHeight;
        if ($this->screenWidth !== null)       $data['ScreenWidth']       = $this->screenWidth;
        if ($this->timeZone !== null)          $data['TimeZone']          = $this->timeZone;
        if ($this->userAgent !== null)         $data['UserAgent']         = $this->userAgent;
        if ($this->ip !== null)                $data['IP']                = $this->ip;
        if ($this->javaEnabled !== null)       $data['JavaEnabled']       = $this->javaEnabled;
        if ($this->javascriptEnabled !== null) $data['JavascriptEnabled'] = $this->javascriptEnabled;
        if ($this->colorDepth !== null)        $data['ColorDepth']        = $this->colorDepth;
        return $data;
    }

    /**
     * Convenience factory: build from a PHP $_SERVER superglobal array.
     */
    public static function fromServerGlobals(array $server = []): self
    {
        $info = new self();
        if (isset($server['HTTP_ACCEPT']))          $info->setAcceptHeader($server['HTTP_ACCEPT']);
        if (isset($server['HTTP_USER_AGENT']))      $info->setUserAgent($server['HTTP_USER_AGENT']);
        if (isset($server['HTTP_ACCEPT_LANGUAGE'])) $info->setLanguage(substr($server['HTTP_ACCEPT_LANGUAGE'], 0, 8));
        if (isset($server['REMOTE_ADDR']))          $info->setIp($server['REMOTE_ADDR']);
        $info->setJavascriptEnabled(true);
        $info->setJavaEnabled(false);
        return $info;
    }
}
