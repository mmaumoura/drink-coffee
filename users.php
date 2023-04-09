<?php

require_once 'App/Controllers/UserController.php';

$controller = new UserController();
$action = $_SERVER['REQUEST_METHOD'];
$endpoint = $_SERVER['REQUEST_URI'];
$endpoint = strtok($endpoint, '?');
$id = intval($_GET['id'] ?? '');

if ($action == 'POST' && $endpoint == '/drink-coffee/users') {
    $controller->addUser();
} elseif ($action == 'POST' && $endpoint == '/drink-coffee/login') {
    $controller->login();
} elseif ($action == 'GET' && preg_match('/^\/drink-coffee\/users\/(\d+)$/', $endpoint, $matches)) {
    $controller->findUserById($matches);
} elseif ($action == 'GET' && $endpoint == '/drink-coffee/users') {
    $controller->getUsers();
} elseif ($action == 'PUT') {
    $controller->updateUser($id);
} elseif ($action == 'DELETE') {
    $controller->deleteUser($id);
} elseif ($action == 'POST' && preg_match('/^\/drink-coffee\/users\/(\d+)\/drink$/', $endpoint, $matches)) {
    $controller->updateDrinkCounter($matches);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
    exit;
}