<?php

require_once 'App/Config/connect.php';

class User extends Connect { 

    private $table;

    public function __construct(){
        parent::__construct();
        $this->table = 'users';
    }

    public function addUser($name, $email, $password, $drinkCounter) {
        $stmt = $this->connect()->prepare("INSERT INTO users (name, email, password, drink_counter) VALUES (:name, :email, :password, :drink_counter)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':drink_counter', $drinkCounter);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
    public function getUsers() {
        $stmt = $this->connect()->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findUserById($id){
        $stmt = $this->connect()->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateUser($id, $name, $email, $password) {
        $stmt = $this->connect()->prepare("UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function deleteUser($id) {
        $stmt = $this->connect()->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function findUserByEmail($email){
        $stmt = $this->connect()->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function storeToken($id, $token) {
        $datetime = new DateTime();
        $datetime = $datetime->format('Y-m-d H:i:s');
        $stmt = $this->connect()->prepare('UPDATE users SET token = :token, token_created_at = :token_created_at WHERE id = :id');
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':token_created_at', $datetime);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function findUserByToken($token)
    {
        $stmt = $this->connect()->prepare('SELECT id FROM users WHERE token = :token');
        $stmt->execute([$token]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ? $rows[0]['id'] : null;
    }

    public function updateDrinkCounter($id) {
        $stmt = $this->connect()->prepare("UPDATE users SET drink_counter = drink_counter + 1 WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $result = $stmt->execute();
        return $result;
    }
}

?>
