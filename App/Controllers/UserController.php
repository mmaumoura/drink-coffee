<?php

require_once 'App/Models/User.php';

class UserController {
    private $model;

    function __construct(){
        $this->model = new User;
    }

    public function addUser(){
        if(empty($_POST['name']) || empty($_POST['email']) || empty(($_POST['password']))){
            http_response_code(400);
            echo json_encode(['error' => 'All fields must be filled.']);
            exit;
        }
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $drinkCounter = 0;
        if(strlen($name) > 255 || strlen($email) > 100 || strlen($password) > 255) {
            http_response_code(400);
            echo json_encode(['error' => 'Check the fields, one of them may have a value greater than allowed.']);
            exit;
        }
        $findUserByEmail = $this->model->findUserByEmail($email);
        if (!empty($findUserByEmail)) {
            http_response_code(409);
            echo json_encode(['error' => 'This email is already being used by another user.']);
            exit;
        }
        $result = $this->model->addUser($name, $email, $password, $drinkCounter);
        if(!empty($result)){
            http_response_code(200);
            echo json_encode(['success' => 'User entered successfully.']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'There was an error adding the user, please try again..']);
            exit;
        }
    }

    public function login(){
        if(empty($_POST['email']) || empty(($_POST['password']))){
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required.']);
            exit;
        }
        $email = $_POST['email'];
        $password = $_POST['password'];
        $findUserByEmail = $this->model->findUserByEmail($email);
        if(empty($findUserByEmail) || !password_verify($password, $findUserByEmail['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password.']);
            exit;
        }
        $token = bin2hex(random_bytes(32));
        $this->model->storeToken($findUserByEmail['id'], $token);
        http_response_code(200);
        echo json_encode(['token' => $token, 'iduser' => $findUserByEmail['id'], 'email' => $findUserByEmail['email'], 'name' => $findUserByEmail['name'], 'drinkCounter' => $findUserByEmail['drink_counter']]);
    }

    public function findUserById($matches){
        $id = $matches[1];
        $result = $this->model->findUserById($id);
        if(empty($result)){
            http_response_code(400);
            echo json_encode(['error' => 'There is no user with the given ID.']);
            exit;
        }
        if(!empty($result)){
            http_response_code(200);
            $result = [
                'iduser' => $result['id'],
                'name' => $result['name'],
                'email' => $result['email'],
                'drinkCounter' => $result['drink_counter'],
            ];
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'An error occurred while fetching users.']);
            exit;
        }
    }

    public function getUsers(){
        $this->authorizationToken();
        $result = $this->model->getUsers();
        if(!empty($result)){
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'An error occurred while fetching users.']);
            exit;
        }
    }

    public function updateUser($id){
        $this->authorizationToken();
        if(empty($id)){
            echo json_encode(['error' => 'You must have an id.']);
            exit;
        }
        $findUserById = $this->model->findUserById($id);
        if(empty($findUserById)){
            http_response_code(400);
            echo json_encode(['error' => 'There is no user with the given ID.']);
            exit;
        }
        parse_str(file_get_contents('php://input'), $put_vars);
        if(empty($put_vars['name']) || empty($put_vars['email']) || empty(($put_vars['password']))){
            http_response_code(400);
            echo json_encode(['error' => 'All fields must be filled.']);
            exit;
        }
        $name = $put_vars['name'];
        $email = $put_vars['email'];
        $password = password_hash($put_vars['password'], PASSWORD_DEFAULT);
        if(strlen($name) > 255 || strlen($email) > 100 || strlen($password) > 255) {
            http_response_code(400);
            echo json_encode(['error' => 'Check the fields, one of them may have a value greater than allowed.']);
            exit;
        }
        $findUserByEmail = $this->model->findUserByEmail($email);
        if (!empty($findUserByEmail)) {
            http_response_code(409);
            echo json_encode(['error' => 'This email is already being used by another user.']);
            exit;
        }
        $result = $this->model->updateUser($id, $name, $email, $password);
        if(!empty($result)){
            echo json_encode(['success' => 'User successfully edited.']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'There was an error editing the user, please try again.']);
            exit;
        }
    }

    public function deleteUser($id){
        $this->authorizationToken();
        if(empty($id)){
            echo json_encode(['error' => 'You must have an id.']);
            exit;
        }
        $findUserById = $this->model->findUserById($id);
        if(empty($findUserById)){
            http_response_code(400);
            echo json_encode(['error' => 'There is no user with the given ID.']);
            exit;
        }
        $result = $this->model->deleteUser($id);
        if(!empty($result)){
            echo json_encode(['success' => 'User deleted successfully.']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'There was an error deleting the user, please try again.']);
            exit;
        }
    }

    public function updateDrinkCounter($matches){
        $this->authorizationToken();
        $id = $matches[1];
        $findUserById = $this->model->findUserById($id);
        if(empty($findUserById)){
            http_response_code(400);
            echo json_encode(['error' => 'There is no user with the given ID.']);
            exit;
        }
        $result = $this->model->updateDrinkCounter($id);
        if ($result) {
            http_response_code(200);
            $result = [
                'iduser' => $id,
                'name' => $findUserById['name'],
                'email' => $findUserById['email'],
                'drinkCounter' => $findUserById['drink_counter'],
            ];
            echo json_encode(['success' => $result]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'There was an error updating the drink counter, please try again.']);
            exit;
        }
    }

    public function authorizationToken(){
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Authorization header not found.']);
            exit;
        }
        $token = $headers['Authorization'];
        $user_id = $this->model->findUserByToken($token);
        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token.']);
            exit;
        }
    }
}