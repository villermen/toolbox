<?php

namespace Villermen\Toolbox;

class Session
{
    private const SESSION_NAME = 'TOOLBOX_SESSION';

    private const DATA_DIRECTORY = 'sessions';

    private const SESSION_LIFETIME = 604800; // 1 week

    public static function start(Config $config): self
    {
        $directory = $config->createDataDirectory(self::DATA_DIRECTORY);

        // Refresh session lifetime every visit. File itself should be touched via frequent flash messaging.
        if ($_COOKIE[self::SESSION_NAME] ?? null) {
            session_id($_COOKIE[self::SESSION_NAME]);
        }

        session_start([
            'save_path' => $directory,
            'name' => self::SESSION_NAME,
            'gc_maxlifetime' => self::SESSION_LIFETIME,
            'gc_probability' => 1,
            'gc_divisor' => 100,
            'cookie_lifetime' => self::SESSION_LIFETIME,
            'cookie_path' => $config->publicPath,
            'cookie_httponly' => true,
            'cookie_secure' => $config->publicProtocol === 'https',
        ]);
  
        return new self();
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
