<?php

namespace Core\Session;

use Core\Message\Message;

final class Session
{
    private static $instance;

    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    private function __construct()
    {
        if (!session_id()) {
            session_save_path(CONFIG_SESSION_PATH);
            session_start();
        }
    }

    public function __get($name)
    {
        if (!empty($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return null;
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    final private function __clone()
    {
    }

    public function all(): ?object
    {
        return (object) $_SESSION;
    }

    public function set(string $name, $value): Session
    {
        $_SESSION[$name] = (is_array($value))?((object)$value):$value;
        return $this;
    }

    public function unset(string $name): Session
    {
        unset($_SESSION[$name]);
        return $this;
    }

    public function has(string $name): bool
    {
        return isset($_SESSION[$name]);
    }

    public function regenerate(): Session
    {
        session_regenerate_id(true);
        return $this;
    }

    public function destroy(): Session
    {
        session_destroy();
        return $this;
    }

    public function flash(): ?Message
    {
        if (!$this->has("flash")) {
            return null;
        }
        $flash = $this->flash;
        $this->unset("flash");
        return $flash;
    }

    public function csrf(): void
    {
        $_SESSION["csrf_token"] = base64_encode(random_bytes(20));
    }
}
