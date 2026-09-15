<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = 'localhost';
$dbname = 'hiace_express';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

function validatePhoneNumber($phone) {
    return preg_match('/^(98|97|96)[0-9]{8}$/', $phone);
}

function calculateCommission($amount) {
    return round($amount * 0.10);
}

function calculateOwnerEarning($amount) {
    return $amount - calculateCommission($amount);
}

function getDepartureTime($vehicleId, $pdo) {
    $stmt = $pdo->prepare("SELECT departure_time FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicleId]);
    $result = $stmt->fetch();
    return $result ? $result['departure_time'] : null;
}

function isVehicleDeparted($vehicleId, $travelDate, $pdo) {
    $today = date('Y-m-d');
    if ($travelDate < $today) return true;
    if ($travelDate > $today) return false;
    
    $departureTime = getDepartureTime($vehicleId, $pdo);
    if (!$departureTime) return false;
    
    $now = new DateTime();
    $departure = new DateTime($travelDate . ' ' . $departureTime);
    return $now > $departure;
}
?>