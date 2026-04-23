<?php

declare(strict_types=1);

class TTwoFactor {
    private TMySQL $db;

    public function __construct(TMySQL $db) {
        $this->db = $db;
    }

    public function enable2FA(int $userId): string {
        /*$totp = TOTP::create();
        $secret = $totp->getSecret();*/

        $tfa = new RobThree\Auth\TwoFactorAuth(new RobThree\Auth\Providers\Qr\QRServerProvider());
        $secret = $tfa->createSecret();

        $stmt = $this->db->prepare("UPDATE uzivatele SET uzi_code_secret = ?, uzi_fc_code_status = 2 WHERE uzi_pk_id = ?");
        $stmt->execute([$secret, $userId]);

        return $secret;
    }

    public function verifyCode(int $userId, string $code): bool {
        $stmt = $this->db->prepare("SELECT uzi_code_secret AS totp_secret FROM uzivatele WHERE uzi_pk_id = ?");
        $stmt->execute([$userId]);
        $secret = $stmt->fetchColumn();
        if (!$secret)
            return false;

        /*$totp = TOTP::create($secret);
        return $totp->verify($code);*/

        $tfa = new RobThree\Auth\TwoFactorAuth(new RobThree\Auth\Providers\Qr\QRServerProvider());
        return $tfa->verifyCode($secret['totp_secret'], $code);
    }

    public function getQRCodeDataUri(int $userId, string $label = 'MyApp'): string {
        $stmt = $this->db->prepare("SELECT  uzi_code_secret AS totp_secret FROM uzivatele WHERE uzi_pk_id = ?");
        $stmt->execute([$userId]);
        $secret = $stmt->fetchColumn();
        if (!$secret)
            return '';

        // Vytvoření TOTP
        /*$totp = TOTP::create($secret);
        $totp->setLabel($label);
        $uri = $totp->getProvisioningUri();
        $qrCode = new QRcode($uri, new Encoding('UTF-8'), ErrorCorrectionLevel::High, 200, 20);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getDataUri(); */

        $tfa = new RobThree\Auth\TwoFactorAuth(new RobThree\Auth\Providers\Qr\QRServerProvider());
        return $tfa->getQRCodeUrl($label, $secret);
    }
}

