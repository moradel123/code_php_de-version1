<?php
// إعدادات الجلسة الآمنة
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'true');
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

session_start();

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight request (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'message' => '', 'user' => null];

// التحقق من وجود بيانات الإدخال
if (empty($data['email']) || empty($data['password'])) {
    $response['message'] = 'Email et mot de passe requis';
    echo json_encode($response);
    exit;
}

try {
    // البحث عن المستخدم في قاعدة البيانات
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // التحقق من وجود المستخدم
    if (!$user) {
        $response['message'] = 'Email ou mot de passe incorrect';
        echo json_encode($response);
        exit;
    }

    // التحقق من كلمة المرور
    if (password_verify($data['password'], $user['password'])) {
        // إعداد بيانات المستخدم للإرسال
        $userData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // تخزين بيانات الجلسة
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        $response['success'] = true;
        $response['message'] = 'Connexion réussie';
        $response['user'] = $userData;
    } else {
        $response['message'] = 'Email ou mot de passe incorrect';
    }
} catch (PDOException $e) {
    $response['message'] = 'Erreur de base de données: ' . $e->getMessage();
}

// إرسال الاستجابة
echo json_encode($response);
?>