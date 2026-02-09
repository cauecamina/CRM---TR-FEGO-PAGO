<?php
require_once __DIR__ . '/../config.php';

function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Retorna erro JSON para o frontend lidar
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Erro de conexão com Banco de Dados: " . $e->getMessage()]);
        exit;
    }
}
