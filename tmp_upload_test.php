<?php
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/upload_handler.php';

// Prepare dummy $_FILES array
$tmp1 = tempnam(sys_get_temp_dir(), 'img');
$tmp2 = tempnam(sys_get_temp_dir(), 'img');
file_put_contents($tmp1, base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII="));
file_put_contents($tmp2, base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII="));

$_FILES = [
    'pg_images' => [
        'name' => ['test1.png', 'test2.png'],
        'type' => ['image/png', 'image/png'],
        'tmp_name' => [$tmp1, $tmp2],
        'error' => [0, 0],
        'size' => [filesize($tmp1), filesize($tmp2)]
    ]
];

try {
    $uid = 2; // Owner demo account ID, or just anything
    $images_uploaded = handle_multiple_uploads($_FILES['pg_images'], 'pg_images/'.$uid, true);
    print_r($images_uploaded);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
