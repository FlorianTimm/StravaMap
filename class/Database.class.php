<?PHP
require_once "Config.class.php";

class Database
{
    private $db = null;

    private static $instance = null;
    
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private function __construct()
    {

        if ($this->db != null) return $this->db;
        try {
            $this->db = new PDO("mysql:host=" . Config::$servername . ";dbname=" . Config::$dbname, Config::$username, Config::$password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            return $this->db;
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
            return null;
        }
    }

    private function __destruct()
    {
        $this->db = null;
    }

    public function prepare($sql)
    {
        return $this->db->prepare($sql);
    }
}
