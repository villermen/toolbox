<?php

namespace Villermen\Toolbox;

class Session
{
    private const SESSION_NAME = 'TOOLBOX_SESSION';

    public static function start(Config $config): self
    {
        // Note: Using /tmp requires PrivateTmp=false in apache2 unit or session will be cleared on restart.
        $sessionPath = sprintf('%s/%s', sys_get_temp_dir(), self::SESSION_NAME);
        if (!file_exists($sessionPath)) {
            mkdir($sessionPath);
        }
        // Refresh session lifetime every visit.
        if ($_COOKIE[self::SESSION_NAME] ?? null) {
            session_id($_COOKIE[self::SESSION_NAME]);
        }
        session_start([
            'save_path' => $sessionPath,
            'name' => self::SESSION_NAME,
            'gc_maxlifetime' => 604800, // week
            'cookie_lifetime' => 604800,
            'cookie_path' => $config->publicPath,
            'cookie_httponly' => true,
            'cookie_secure' => $config->publicProtocol === 'https',
        ]);
  
        return new self();
    }

    private function __construct()
    {
    }

    public function get(string $key): mixed
    {
        return ($_SESSION[$key] ?? null);
    }

    public function set(string $key, mixed $value): void
    {
        if ($value !== null) {
            $_SESSION[$key] = $value;
        } else {
            unset($_SESSION[$key]);
        }
    }
}
