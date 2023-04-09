<?php 

class Connect {
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'drinkcoffee';
    private const DB_USER = 'root';
    private const DB_PASSWORD = '';

    private $pdo;

    public function __construct(){
        $this->connect();
    }

    public function connect(){
        try {
            $this->pdo = new PDO("mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME, self::DB_USER, self::DB_PASSWORD);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $this->pdo;
        } catch (PDOException $e) {
            echo "An error occurred while connecting to the database: " . $e->getMessage();
        }
    }
}

?>
