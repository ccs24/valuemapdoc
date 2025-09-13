<?php
// classes/local/session_helper.php

namespace mod_valuemapdoc\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Class session_helper
 */
class session_helper {

    const SESSION_KEY = 'valuemapdoc';

    /**
     * Zapisz całą sesję dokumentu.
     *
     * @param array $data Klucz => wartość
     */
    public static function save(array $data): void {
        $_SESSION[self::SESSION_KEY] = $data;
    }

    /**
     * Zaktualizuj pojedyncze pole w sesji.
     *
     * @param string $key Klucz
     * @param mixed $value Wartość
     */
    public static function set(string $key, $value): void {
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }

    /**
     * Pobierz dane sesji.
     *
     * @param string|null $key Jeśli podano klucz, zwraca tylko to pole.
     * @return mixed
     */
    public static function get(string $key = null) {
        if ($key === null) {
            return $_SESSION[self::SESSION_KEY] ?? [];
        }
        return $_SESSION[self::SESSION_KEY][$key] ?? null;
    }

    /**
     * Czy sesja istnieje?
     *
     * @return bool
     */
    public static function exists(): bool {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Wyczyść całą sesję dokumentu.
     */
    public static function clear(): void {
        unset($_SESSION[self::SESSION_KEY]);
    }
}