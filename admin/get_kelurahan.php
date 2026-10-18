<?php
require_once '../config/database.php';

/** @var PDO $pdo */

$id_kecamatan = isset($_GET['id_kecamatan']) ? (int)$_GET['id_kecamatan'] : 0;

$stmt = $pdo->prepare("SELECT id_kelurahan, nama_kelurahan FROM kelurahan WHERE id_kecamatan = :id_kecamatan AND status = 1 ORDER BY nama_kelurahan ASC");
$stmt->execute([':id_kecamatan' => $id_kecamatan]);

$data = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($data);