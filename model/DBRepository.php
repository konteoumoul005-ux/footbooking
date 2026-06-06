<?php
error_reporting(0);
ini_set('display_errors', 0);

class DBRepository {
    protected $host;
    protected $dbname;
    protected $user;
    protected $password;
    protected $db;

    public function __construct() {
        $this->host = getenv('DB_HOST');
        $this->dbname = getenv('DB_NAME');
        $this->user = getenv('DB_USER');
        $this->password = getenv('DB_PASSWORD');
        
        try {
            $this->db = new PDO(
                "pgsql:host={$this->host};dbname={$this->dbname}",
                $this->user,
                $this->password
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function query($sql) {
        return $this->db->query($sql);
    }

    public function getPDO() {
        return $this->db;
    }
}
?>