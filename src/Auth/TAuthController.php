<?php

declare(strict_types=1);

class TAuthController {
    private TMySQL $db;
    private TSessionManager $sessionManager;
    private TTwoFactor $twoFactor;
    private bool $cookie_secure = false;

    public function __construct(TMySQL $db) {
        $this->db            = $db;
        $this->sessionManager = new TSessionManager($db);
        $this->twoFactor      = new TTwoFactor($db);

        // pro HTTPS true, pro HTTP false
        // do nginx pridat fastcgi_param HTTP_X_FORWARDED_PROTO $scheme;
        $this->cookie_secure =
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    public function login(string $login, string $password, ?string $totpCode = null): void {

        $user = new User($this->db);

        if (!$user->loadByLogin($login) || !$user->verifyPassword($password)) {
            http_response_code(401);
            exit("Invalid credentials");
        }

        // pokud má 2FA enabled
        if ($user->is2FAEnabled()) {
            if (!$totpCode || !$this->twoFactor->verifyCode($user->getUserId(), $totpCode)) {
                http_response_code(401);
                exit("Invalid 2FA code");
            }
        }

        $device = new TDevice($this->db);
        $device->save($user->getUserId());

        $tokens = $this->sessionManager->createSession($user->getUserId(), $device->getDeviceId());

        setcookie('access_token', $tokens['access']['raw'], [
            'expires'=>$tokens['access']['expires'],
            'path'=>'/',
            'secure'=>$this->cookie_secure,
            'httponly'=>true,
            'samesite'=>'Strict'
        ]);

        $_COOKIE['access_token'] = $tokens['access']['raw'];
        
        setcookie('refresh_token', $tokens['refresh']['raw'], [
            'expires'=>$tokens['refresh']['expires'],
            'path'=>'/',
            'secure'=>$this->cookie_secure,
            'httponly'=>true,
            'samesite'=>'Strict'
        ]);

        setcookie('device_id', $device->getDeviceId(), [
            'expires'=>$tokens['refresh']['expires'],
            'path'=>'/',
            'secure'=>$this->cookie_secure,
            'httponly'=>true,
            'samesite'=>'Strict'
        ]);

        //echo "Logged in";
    }

    public function logout(): void {
        if (!empty($_COOKIE['refresh_token'])) {
            $this->sessionManager->revokeRefresh($_COOKIE['refresh_token']);
        }

        setcookie('access_token',   '', time()-3600, '/');
        setcookie('refresh_token',  '', time()-3600, '/');
        setcookie('device_id',      '', time()-3600, '/');

        //echo "Logged out";
    }

    public function refresh(): bool {
        $refreshToken = $_COOKIE['refresh_token'] ?? null;
        if (!$refreshToken) {
            return false;
        }

        $tokens = $this->sessionManager->rotateRefresh($refreshToken);
        if (!$tokens) {
            return false;
        }

        setcookie('access_token', $tokens['access']['raw'], [
            'expires'=>$tokens['access']['expires'],
            'path'=>'/',
            //'secure'=>true, pouze pro HTTPS
            'secure'=>$this->cookie_secure,
            'httponly'=>true,
            'samesite'=>'Strict'
        ]);

        // DŮLEŽITÉ: aby šel nový access token použít ještě v tomhle requestu
        $_COOKIE['access_token'] = $tokens['access']['raw'];

        setcookie('refresh_token', $tokens['refresh']['raw'], [
            'expires'=>$tokens['refresh']['expires'],
            'path'=>'/',
            //'secure'=>true, pouze pro HTTPS
            'secure'=>$this->cookie_secure,
            'httponly'=>true,
            'samesite'=>'Strict'
        ]);

        // Stejný důvod jako u access tokenu (rotujete refresh token)
        $_COOKIE['refresh_token'] = $tokens['refresh']['raw'];

        return true;
    }

    public function checkAccess(): ?int {
        $accessToken = $_COOKIE['access_token'] ?? null;
        if (!$accessToken) return null;
        return $this->sessionManager->validateAccess($accessToken);
    }

    public function isLoggedIn(): bool {
        return $this->checkAccess() !== null;
    }

    public function hasAccessCookie(): bool {
        return !empty($_COOKIE['access_token']);
    }

    public function tryCheckAccess(): ?int {
        $userId = $this->checkAccess();
        if ($userId !== null) {
            return $userId;
        }

        if (empty($_COOKIE['refresh_token'])) {
            return null;
        }

        if (!$this->refresh()) {
            return null;
        }

        return $this->checkAccess();
    }

    /**
     * Zkusí získat přihlášeného uživatele jako objekt User.
     * - použije tryCheckAccess() (tj. včetně tichého refresh)
     * - když nejde ověřit, vrátí null
     */
    public function tryGetUser(): ?User {
        $userId = $this->tryCheckAccess();
        if ($userId === null) {
            return null;
        }

        $user = new User($this->db);
        if (!$user->loadById($userId)) {
            return null;
        }

        return $user;
    }

    /**
     * "Middleware" pro stránky:
     * - vrátí User, pokud je přihlášen (včetně tichého refresh)
     * - když není, přesměruje na login a ukončí skript
     *
     * $loginPath: relativní/absolutní URL na login stránku
     */
    public function requireUser(string $loginPath = '/htm/login/'): User {
        $user = $this->tryGetUser();
        if ($user !== null) {
            return $user;
        }

        header('Location: ' . $loginPath);
        exit;
    }

    public function generate2FASecret(int $userId): string {
        return $this->twoFactor->enable2FA($userId);
    }

    public function get2FAQRCode(int $userId, string $label = 'myTEDOM'): string {
        return $this->twoFactor->getQRCodeDataUri($userId, $label);
    }

    public function authenticateBasic(): ?int {
        $headers = getallheaders();
        if (empty($headers['Authorization'])) return null;

        if (!preg_match('/Basic\s+(.*)$/i', $headers['Authorization'], $matches)) return null;

        $decoded = base64_decode($matches[1]);
        [$login, $password] = explode(':', $decoded, 2);

        $user = new User($this->db);
        if (!$user->loadByLogin($login) || !$user->verifyPassword($password)) return null;

        // pokud má 2FA aktivní, Basic Auth vyžaduje předání kódu jinak selže
        if ($user->is2FAEnabled()) return null;

        return $user->getUserId();
    }

    public function authenticateBearer(): ?int {
        $headers = getallheaders();
        if (empty($headers['Authorization'])) return null;

        if (!preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) return null;

        $accessToken = $matches[1];
        return $this->sessionManager->validateAccess($accessToken);
    }

    // ---------------- API univerzální ----------------
    public function authenticateAPI(): ?int {
        return $this->authenticateBearer() ?? $this->authenticateBasic();
    }
}

