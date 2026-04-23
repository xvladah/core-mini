<?php

declare(strict_types=1);

class TDevice {

    const string HASH_ALGORITHM = 'sha256';

    private TMySQL $db;

    private ?string $deviceId;
    private string $device;
    private string $userAgentHash;

    private ?string $ipText = null;

    private ?string $xForwardedFor = null;
    private ?string $remoteAddr = null;

    private string $userAgent = '';
    private string $acceptLanguage = '';
    private string $accept = '';
    private string $referer = '';
    private string $origin = '';

    private string $secChUa = '';
    private string $secChUaPlatform = '';
    private string $secChUaMobile = '';    

    public function __construct(TMySQL $db) {
        $this->db = $db;
        $this->deviceId = null;
        $this->device = $this->uuidv4();
        $this->collectFromRequest();
    }

    /**
     * @throws RandomException
     */
    private function uuidv4(): string
    {
        $data = random_bytes(16);

        // verze 4
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // variant RFC 4122
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Sem dej IP adresy (nebo CIDR rozsahy) svých důvěryhodných proxy.
     * Pokud to nevyplníš, REMOTE_ADDR zůstane zdroj pravdy a XFF se jen uloží.
     */
    private array $trustedProxies = [
        '127.0.0.1',
        '::1',
        '172.27.2.222',
    ];

    private function collectFromRequest(): void {
        $this->userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->userAgentHash = hash(self::HASH_ALGORITHM, $this->userAgent);

        $this->acceptLanguage = (string)($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        $this->accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
        $this->referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
        $this->origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');

        $this->secChUa = (string)($_SERVER['HTTP_SEC_CH_UA'] ?? '');
        $this->secChUaPlatform = (string)($_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '');
        $this->secChUaMobile = (string)($_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '');

        $this->xForwardedFor = $this->getXForwardedForRaw();
        $this->remoteAddr = $this->getRemoteAddr();

        $this->ipText = $this->getClientIpText();
    }

    private function getXForwardedForRaw(): ?string {
        $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        if (!is_string($xff) || trim($xff) === '') {
            return null;
        }
        return $xff;
    }

    private function getRemoteAddr(): ?string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (!is_string($ip) || $ip === '') {
            return null;
        }
        return $ip;
    }

    private function isTrustedProxy(?string $remoteAddr): bool {
        if ($remoteAddr === null) {
            return false;
        }
        return in_array($remoteAddr, $this->trustedProxies, true);
    }

    /**
     * Bezpečný základ:
     * - když nejsi za trusted proxy: REMOTE_ADDR
     * - když jsi za trusted proxy: první validní IP z X-Forwarded-For, jinak REMOTE_ADDR
     */
    private function getClientIpText(): ?string {
        $remoteAddr = $this->remoteAddr;

        if (!$this->isTrustedProxy($remoteAddr)) {
            return $remoteAddr;
        }

        $ipFromXff = $this->extractClientIpFromXForwardedFor($this->xForwardedFor);
        return $ipFromXff ?? $remoteAddr;
    }

    private function extractClientIpFromXForwardedFor(?string $xff): ?string {
        if ($xff === null) return null;

        $parts = explode(',', $xff);
        foreach ($parts as $part) {
            $candidate = trim($part);

            if ($candidate === '' || strcasecmp($candidate, 'unknown') === 0) {
                continue;
            }

            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return null;
    }

    public function save(int $userId): void {
        $stmt = $this->db->prepare("
            INSERT INTO uzivatele_devices (
                uzd_fk_uzivatele,
                uzd_device,
                uzd_user_agent,
                uzd_user_agent_hash,
                uzd_ip,
                uzd_x_forwarded_for,
                uzd_remote_addr,
                uzd_accept,
                uzd_accept_language,                
                uzd_referer,
                uzd_origin,
                uzd_ua,
                uzd_ua_platform,
                uzd_ua_mobile,
                uzd_datumpz
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $datumpz = time();

        $stmt->bind([
            $userId,
            $this->device,
            $this->userAgent,
            $this->userAgentHash,
            $this->ipText,
            $this->xForwardedFor,
            $this->remoteAddr,
            $this->accept,
            $this->acceptLanguage,
            $this->referer,
            $this->origin,
            $this->secChUa,
            $this->secChUaPlatform,
            $this->secChUaMobile,
            $datumpz
        ]);
        $stmt->execute();

        $this->deviceId = (string)$this->db->getLastInsertId();
    }

    public function getDeviceId(): ?string {
        return $this->deviceId;
    }
}

