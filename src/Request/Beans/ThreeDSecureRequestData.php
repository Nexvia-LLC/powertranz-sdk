<?php

declare(strict_types=1);

namespace PowerTranz\Request\Beans;

use PowerTranz\Enums\ChallengeIndicator;
use PowerTranz\Enums\ChallengeWindowSize;

/**
 * 3-D Secure request parameters (challenge window, channel, authentication indicators,
 * message category, transaction type, account info blocks, etc.).
 */
class ThreeDSecureRequestData
{
    // Fields sent back to gateway when pre-authenticated (non-SPI flow)
    private ?string $eci                    = null;
    private ?string $cavv                   = null;
    private ?string $xid                    = null;
    private ?string $authenticationStatus   = null;
    private ?string $protocolVersion        = null;
    private ?string $dsTransId              = null;

    // Challenge configuration
    private int    $challengeWindowSize  = 4;
    private string $challengeIndicator   = '01';
    private string $channelIndicator     = '02'; // '02' = Browser
    private ?string $riIndicator         = null;
    private ?string $authenticationIndicator = null;
    private ?string $messageCategory     = null;
    private ?string $transactionType     = null;

    // Account information for 3DS2
    private ?AccountInfoRequestData $accountInfo = null;

    public function setChallengeWindowSize(int $v): void         { $this->challengeWindowSize = $v; }
    public function setChallengeIndicator(string $v): void       { $this->challengeIndicator = $v; }
    public function setChannelIndicator(string $v): void         { $this->channelIndicator = $v; }
    public function setRiIndicator(string $v): void              { $this->riIndicator = $v; }
    public function setAuthenticationIndicator(string $v): void  { $this->authenticationIndicator = $v; }
    public function setMessageCategory(string $v): void          { $this->messageCategory = $v; }
    public function setTransactionType(string $v): void          { $this->transactionType = $v; }
    public function setEci(string $v): void                      { $this->eci = $v; }
    public function setCavv(string $v): void                     { $this->cavv = $v; }
    public function setXid(string $v): void                      { $this->xid = $v; }
    public function setAuthenticationStatus(string $v): void     { $this->authenticationStatus = $v; }
    public function setProtocolVersion(string $v): void          { $this->protocolVersion = $v; }
    public function setDsTransId(string $v): void                { $this->dsTransId = $v; }
    public function setAccountInfo(AccountInfoRequestData $v): void { $this->accountInfo = $v; }

    public function getChallengeWindowSize(): int    { return $this->challengeWindowSize; }
    public function getChallengeIndicator(): string  { return $this->challengeIndicator; }

    public function toArray(): array
    {
        $data = [
            'ChallengeWindowSize' => $this->challengeWindowSize,
            'ChallengeIndicator'  => $this->challengeIndicator,
            'ChannelIndicator'    => $this->channelIndicator,
        ];

        if ($this->riIndicator !== null)              $data['RiIndicator']             = $this->riIndicator;
        if ($this->authenticationIndicator !== null)  $data['AuthenticationIndicator'] = $this->authenticationIndicator;
        if ($this->messageCategory !== null)          $data['MessageCategory']         = $this->messageCategory;
        if ($this->transactionType !== null)          $data['TransactionType']         = $this->transactionType;
        if ($this->eci !== null)                      $data['Eci']                     = $this->eci;
        if ($this->cavv !== null)                     $data['Cavv']                    = $this->cavv;
        if ($this->xid !== null)                      $data['Xid']                     = $this->xid;
        if ($this->authenticationStatus !== null)     $data['AuthenticationStatus']    = $this->authenticationStatus;
        if ($this->protocolVersion !== null)          $data['ProtocolVersion']         = $this->protocolVersion;
        if ($this->dsTransId !== null)                $data['DsTransId']               = $this->dsTransId;
        if ($this->accountInfo !== null)              $data['AccountInfo']             = $this->accountInfo->toArray();

        return $data;
    }
}
