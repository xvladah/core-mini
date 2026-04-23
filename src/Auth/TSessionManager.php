<?php

declare(strict_types=1);

class TSessionManager {
    private TMySQL $db;

    const int ACCESS_EXPIRATION_SECONDS = 900;
    const int REFRESH_EXPIRATION_SECONDS = 3600*24*30;
    const string HASH_ALGORITHM = 'sha256';

    public function __construct(TMySQL $db) {
        $this->db = $db;
    }

    private function generateToken(): array {
        $raw = bin2hex(random_bytes(32));
        $hash = hash(self::HASH_ALGORITHM, $raw);
        return [$raw, $hash];
    }

    public function createSession(int $userId, ?string $deviceId): array {
        [$accessRaw, $accessHash]   = $this->generateToken();
        [$refreshRaw, $refreshHash] = $this->generateToken();

        $accessExp  = time() + self::ACCESS_EXPIRATION_SECONDS;
        $refreshExp = time() + self::REFRESH_EXPIRATION_SECONDS;

        $stmt = $this->db->prepare(
            "INSERT INTO uzivatele_sessions (uzs_fk_uzivatele, uzs_fk_uzivatele_devices, uzs_access_hash, uzs_refresh_hash, uzs_access_hash_expires, uzs_refresh_hash_expires, uzs_fc_status, uzs_datumpz)".
                  "VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind([$userId, $deviceId, $accessHash, $refreshHash, $accessExp, $refreshExp, 1, time()]);
        $stmt->execute();

        return [
            'access'  => ['raw'=>$accessRaw, 'expires'=>$accessExp],
            'refresh' => ['raw'=>$refreshRaw, 'expires'=>$refreshExp]
        ];
    }

    public function rotateRefresh(string $oldRefreshToken): ?array {
        $oldHash = hash('sha256', $oldRefreshToken);

        // Nové tokeny připravíme předem
        [$newAccessRaw, $newAccessHash] = $this->generateToken();
        [$newRefreshRaw, $newRefreshHash] = $this->generateToken();

        $newAccessExpUnix = time() + self::ACCESS_EXPIRATION_SECONDS;
        $newRefreshExpUnix = time() + self::REFRESH_EXPIRATION_SECONDS;

        $this->db->beginTransaction();
        try {
            // Zamkneme řádek pro update, aby 2 paralelní refreshe neudělaly závod
            $stmt = $this->db->prepare(
                "SELECT uzs_refresh_hash_expires AS refresh_expires ".
                "FROM uzivatele_sessions ".
                "WHERE uzs_refresh_hash = ? AND uzs_fc_status = 1 ".
                "FOR UPDATE");
            $stmt->bind([$oldHash]);
            $stmt->execute();
            $session = $stmt->fetch();

            if (!$session || $session['refresh_expires'] < time()) {
                $this->db->rollbackTransaction();
                return null;
            }

            // Rotace „in-place“: přepíšeme hashe a expirace v tom samém řádku
            $stmt2 = $this->db->prepare(
                "UPDATE uzivatele_sessions ".
                      "SET uzs_access_hash = ?, uzs_refresh_hash = ?, uzs_access_hash_expires = ?, uzs_refresh_hash_expires = ? ".
                      "WHERE uzs_refresh_hash = ? AND uzs_fc_status = 1");
            $stmt2->bind([$newAccessHash, $newRefreshHash, $newAccessExpUnix, $newRefreshExpUnix, $oldHash]);
            $stmt2->execute();

            $this->db->commitTransaction();

            return [
                'access'  => ['raw' => $newAccessRaw,  'expires' => $newAccessExpUnix],
                'refresh' => ['raw' => $newRefreshRaw, 'expires' => $newRefreshExpUnix],
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollbackTransaction();
            }
            throw $e;
        }
    }

    public function revokeRefresh(string $refreshToken): void {
        $hash = hash(self::HASH_ALGORITHM, $refreshToken);

        $stmt = $this->db->prepare("UPDATE uzivatele_sessions SET uzs_fc_status = 0 WHERE uzs_refresh_hash = ?");
        $stmt->bind([$hash]);
        $stmt->execute();
    }

    public function validateAccess(string $accessToken): ?int {
        $hash = hash(self::HASH_ALGORITHM, $accessToken);
        $stmt = $this->db->prepare(
            "SELECT uzs_fk_uzivatele AS user_id, uzs_access_hash_expires AS access_expires ".
                  "FROM uzivatele_sessions ".
                  "WHERE uzs_access_hash = ? AND uzs_fc_status = 1");

        $stmt->bind([$hash]);
        $stmt->execute();

        $data = $stmt->fetch();
        if (!$data || $data['access_expires'] < time()) return null;
        return (int)$data['user_id'];
    }
}