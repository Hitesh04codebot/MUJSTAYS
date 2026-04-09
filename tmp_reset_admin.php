<?php
$pass = 'Admin@1234';
$hash = '$2y$12$K8REiQGkK8F.3JO1n7S6hOmxAkXCPc8qhvEL0gY9pMj3wRYN44dLgy'; // Full hash from before (careful)
if (password_verify($pass, $hash)) {
    echo "CORRECT PASSWORD! SOMETHING ELSE IS WRONG.\n";
} else {
    echo "WRONG PASSWORD! RESETTING ADMIN PASSWORD...\n";
    require_once 'config/config.php';
    require_once 'includes/db.php';
    $new_hash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->execute([$new_hash, 'admin@mujstays.com']);
    echo "Update Result: " . ($stmt->rowCount() ? "Applied" : "Not Modified") . "\n";
}
