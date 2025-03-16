<?PHP
require_once "Config.class.php";

class Database
{

    private static $db = null;

    public static function getConnection()
    {
        if (self::$db != null) return self::$db;
        try {
            self::$db = new PDO("mysql:host=" . Config::$servername . ";dbname=" . Config::$dbname, Config::$username, Config::$password);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            return self::$db;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
            return null;
        }
    }
}
?>