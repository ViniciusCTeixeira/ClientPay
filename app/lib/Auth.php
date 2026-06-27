<?php

class Auth
{
    private static ?array $cachedUser = null;
    private static int $sessionTimeout = 1800;

    public static function configure(array $config): void
    {
        self::$sessionTimeout = max(300, (int)($config['session_timeout'] ?? 1800));
    }

    private static function attemptKey(string $email, string $ip): string
    {
        return hash('sha256', mb_strtolower(trim($email)) . '|' . $ip);
    }

    private static function assertLoginAllowed(string $key): void
    {
        $stm = Database::pdo()->prepare('SELECT locked_until FROM login_attempts WHERE key_hash=?');
        $stm->execute([$key]);
        $lockedUntil = $stm->fetchColumn();
        if ($lockedUntil && strcmp((string)$lockedUntil, gmdate('Y-m-d H:i:s')) > 0) {
            throw new RuntimeException('Muitas tentativas de acesso. Aguarde 15 minutos e tente novamente.');
        }
    }

    private static function recordFailedLogin(string $key): void
    {
        $pdo = Database::pdo();
        $stm = $pdo->prepare('SELECT attempts, window_started_at FROM login_attempts WHERE key_hash=?');
        $stm->execute([$key]);
        $row = $stm->fetch();
        $now = time();
        $windowStart = $row ? strtotime((string)$row['window_started_at'] . ' UTC') : false;
        $attempts = (!$row || !$windowStart || $windowStart < $now - 900) ? 1 : ((int)$row['attempts'] + 1);
        $startedAt = $attempts === 1 ? gmdate('Y-m-d H:i:s', $now) : (string)$row['window_started_at'];
        $lockedUntil = $attempts >= 5 ? gmdate('Y-m-d H:i:s', $now + 900) : null;

        $upsert = $pdo->prepare(
            'INSERT INTO login_attempts(key_hash,attempts,window_started_at,locked_until) VALUES(?,?,?,?)
             ON CONFLICT(key_hash) DO UPDATE SET attempts=excluded.attempts,
                window_started_at=excluded.window_started_at, locked_until=excluded.locked_until'
        );
        $upsert->execute([$key, $attempts, $startedAt, $lockedUntil]);
    }

    public static function attempt(string $email, string $password, string $ip = ''): bool
    {
        $email = mb_strtolower(trim($email));
        $key = self::attemptKey($email, $ip);
        self::assertLoginAllowed($key);
        $stm = Database::pdo()->prepare('SELECT * FROM users WHERE lower(email) = ? LIMIT 1');
        $stm->execute([$email]);
        $u = $stm->fetch();
        if ($u && password_verify($password, $u['password_hash'])) {
            Database::pdo()->prepare('DELETE FROM login_attempts WHERE key_hash=?')->execute([$key]);
            if (password_needs_rehash($u['password_hash'], PASSWORD_BCRYPT)) {
                self::updatePassword((int)$u['id'], $password);
            }
            session_regenerate_id(true);
            $_SESSION['uid'] = $u['id'];
            $_SESSION['uname'] = $u['name'];
            $_SESSION['uemail'] = $u['email'];
            $_SESSION['urole'] = $u['role'] ?? 'operator';
            $_SESSION['_last_activity'] = time();
            self::$cachedUser = $u;
            Csrf::rotate();
            return true;
        }
        self::recordFailedLogin($key);
        return false;
    }

    public static function user(): ?array
    {
        if (!isset($_SESSION['uid'])) return null;
        if (self::$cachedUser !== null && (int)self::$cachedUser['id'] === (int)$_SESSION['uid']) {
            return self::$cachedUser;
        }
        $stm = Database::pdo()->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$_SESSION['uid']]);
        self::$cachedUser = $stm->fetch() ?: null;
        return self::$cachedUser;
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stm = Database::pdo()->prepare('UPDATE users SET password_hash=? WHERE id=?');
        $stm->execute([$hash, $id]);
    }

    public static function check(): bool
    {
        if (!isset($_SESSION['uid'])) {
            return false;
        }
        if (isset($_SESSION['_last_activity']) && (time() - (int)$_SESSION['_last_activity']) > self::$sessionTimeout) {
            self::logout();
            return false;
        }
        if (!self::user()) {
            self::logout();
            return false;
        }
        $_SESSION['_last_activity'] = time();
        return true;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ?p=auth/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            echo '<div class="alert alert-danger">Acesso permitido somente para administradores.</div>';
            exit;
        }
    }

    public static function logout(): void
    {
        self::$cachedUser = null;
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }
}
