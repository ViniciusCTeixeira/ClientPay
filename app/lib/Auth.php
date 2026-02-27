<?php

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $stm = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stm->execute([$email]);
        $u = $stm->fetch();
        if ($u && password_verify($password, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = $u['id'];
            $_SESSION['uname'] = $u['name'];
            $_SESSION['uemail'] = $u['email'];
            Csrf::rotate();
            return true;
        }
        return false;
    }

    public static function user(): ?array
    {
        if (!isset($_SESSION['uid'])) return null;
        $stm = Database::pdo()->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$_SESSION['uid']]);
        return $stm->fetch() ?: null;
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stm = Database::pdo()->prepare('UPDATE users SET password_hash=? WHERE id=?');
        $stm->execute([$hash, $id]);
    }

    public static function check(): bool
    {
        return isset($_SESSION['uid']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ?p=auth/login');
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }
}
