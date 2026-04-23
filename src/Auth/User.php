<?php

declare(strict_types=1);

class User {
    private int $userId;
    private string $login;
    private string $name;
    private string $firstname;
    private string $lastname;
    private ?string $email;
    private ?string $passwordHash;
    private ?string $totpSecret;
    private ?bool $totpEnabled;
    private TMySQL $db;

    public function __construct(TMySQL $db) {
        $this->db = $db;
    }

    public function loadByLogin(string $login): bool {
        $stmt = $this->db->prepare("SELECT 
                uzi_pk_id AS uzivatel_id, 
                uzi_login AS login,
                uzi_jmeno AS jmeno,
                uzi_prijmeni AS prijmeni,
                uzi_dodatek AS dodatek,
                uzi_email AS email,
                uzi_heslo AS password_hash,
                uzi_code_secret AS totp_secret,
                uzi_fc_code_status AS totp_enabled 
         FROM uzivatele WHERE uzi_login = ?");
        $stmt->bind([$login]);
        $stmt->execute();
        $data = $stmt->fetch();

        return $this->setVariables($data);
    }

    public function loadById(int $userId): bool {
        $stmt = $this->db->prepare("SELECT 
                uzi_pk_id AS uzivatel_id, 
                uzi_login AS login,
                uzi_jmeno AS jmeno,
                uzi_prijmeni AS prijmeni,
                uzi_dodatek AS dodatek,
                uzi_email AS email,
                uzi_heslo AS password_hash,
                uzi_code_secret AS totp_secret,
                uzi_fc_code_status AS totp_enabled
         FROM uzivatele WHERE uzi_pk_id = ?");
        $stmt->bind([$userId]);
        $stmt->execute();
        $data = $stmt->fetch();
        
        return $this->setVariables($data);
    }
    
    protected function setVariables(mixed $data): bool {
        if (!$data) return false;
        
        $this->userId       = (int)$data['uzivatel_id'];
        $this->login        = $data['login'];
        $this->name         = $data['prijmeni'].' '.$data['jmeno'];
        $this->firstname    = $data['jmeno'];
        $this->lastname     = $data['prijmeni'];

        if($data['dodatek'] != '')
            $this->name .= ', '.$data['dodatek'];

        $this->email        = $data['email'];
        $this->passwordHash = $data['password_hash'];
        $this->totpSecret   = $data['totp_secret'] ?? '';
        $this->totpEnabled  = (bool)($data['totp_enabled'] == 2 ? 1 : 0);
        
        return true;
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->passwordHash);
    }

    public function is2FAEnabled(): bool {
        return $this->totpEnabled;
    }

    public function getTOTPSecret(): string {
        return $this->totpSecret;
    }

    public function isAdmin(): bool {
        return array_key_exists($this->getUserId(), TConfig::ADMINS);
    }

    public function getUserId(): int {
        return $this->userId;
    }
    
    public function getLogin(): string {
        return $this->login;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getFirstname(): string {
        return $this->firstname;
    }

    public function getLastname(): string {
        return $this->lastname;
    }

    public function getInitials(): string {
        return $this->firstname[0].$this->lastname[0];
    }

    public function enable2FA(): bool {
        $this->db->prepare("UPDATE uzivatele SET uzi_fc_code_status = 2 WHERE uzi_pk_id = ?");
        $this->db->bind([$this->userId]);
        $this->db->execute();

        $this->totpEnabled = true;
        return true;
    }

    public function disable2FA(): bool {
        $this->db->prepare("UPDATE uzivatele SET uzi_fc_code_status = 0 WHERE uzi_pk_id = ?");
        $this->db->bind([$this->userId]);
        $this->db->execute();

        $this->totpEnabled = false;
        return true;
    }

    public function update2FASecret(string $secret): bool {
        $this->db->prepare("UPDATE uzivatele SET uzi_code_secret = ? WHERE uzi_pk_id = ?");
        $this->db->bind([$secret, $this->userId]);
        $this->db->execute();

        $this->totpSecret = $secret;
        return true;
    }
}
