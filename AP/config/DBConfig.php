<?php
// config/DBConfig.php
class DBConfig {
    private static $host     = '127.0.0.1';
    private static $dbname   = 'portal';
    private static $user     = 'root';
    private static $password = '';

    public static function getConnection() {
        $dsn = 'mysql:host='.self::$host.';dbname='.self::$dbname.';charset=utf8mb4';
        $pdo = new PDO($dsn, self::$user, self::$password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        return $pdo;
    }
}
