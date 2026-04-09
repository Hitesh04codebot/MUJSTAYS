<?php
// includes/upload_handler.php — Centralized File Upload Logic

require_once __DIR__ . '/../config/config.php';

/**
 * Handle a single file upload.
 * Returns relative path on success, throws Exception on failure.
 */
function handle_upload(array $file, string $subdirectory, bool $is_image = true): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (server limit).',
            UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
            UPLOAD_ERR_PARTIAL    => 'File only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
        ];
        throw new Exception($errors[$file['error']] ?? 'Unknown upload error.');
    }

    // --- Size check ---
    $max_bytes = ($is_image ? MAX_FILE_SIZE_MB : MAX_VIDEO_SIZE_MB) * 1024 * 1024;
    if ($file['size'] > $max_bytes) {
        $limit = $is_image ? MAX_FILE_SIZE_MB : MAX_VIDEO_SIZE_MB;
        throw new Exception("File size exceeds {$limit}MB limit.");
    }

    // --- MIME type check via finfo (not extension) ---
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = $is_image ? ALLOWED_IMAGE_TYPES : ALLOWED_VIDEO_TYPES;
    if (!in_array($mime, $allowed, true)) {
        throw new Exception("Invalid file type: {$mime}. Allowed: " . implode(', ', $allowed));
    }

    // --- Determine extension from MIME ---
    $ext_map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'video/mp4'  => 'mp4',
        'video/webm' => 'webm',
    ];
    $ext = $ext_map[$mime] ?? 'bin';

    // --- Generate random safe filename ---
    $filename = bin2hex(random_bytes(16)) . '_' . uniqid() . '.' . $ext;

    // --- Build destination path ---
    $dest_dir = rtrim(UPLOAD_PATH, '/') . '/' . trim($subdirectory, '/') . '/';
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }
    $dest_path = $dest_dir . $filename;

    // --- Compress image if GD available ---
    if ($is_image && extension_loaded('gd')) {
        compress_image($file['tmp_name'], $dest_path, $mime);
    } else {
        if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
            throw new Exception('Failed to move uploaded file.');
        }
    }

    // Return relative path from project root
    return 'uploads/' . trim($subdirectory, '/') . '/' . $filename;
}

/**
 * Compress and resize image using PHP GD
 * Max width: 1200px, JPEG quality: 80
 */
function compress_image(string $source, string $dest, string $mime): void {
    $image = match($mime) {
        'image/jpeg' => imagecreatefromjpeg($source),
        'image/png'  => imagecreatefrompng($source),
        'image/webp' => imagecreatefromwebp($source),
        default      => null,
    };

    if (!$image) {
        copy($source, $dest);
        return;
    }

    $orig_w = imagesx($image);
    $orig_h = imagesy($image);
    $max_w = 1200;

    if ($orig_w > $max_w) {
        $ratio  = $max_w / $orig_w;
        $new_w  = $max_w;
        $new_h  = (int)($orig_h * $ratio);
        $resized = imagecreatetruecolor($new_w, $new_h);

        // Preserve transparency for PNG
        if ($mime === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
        imagedestroy($image);
        $image = $resized;
    }

    // Save as JPEG for size efficiency (except PNG)
    if ($mime === 'image/png') {
        imagepng($image, $dest, 6);
    } else {
        imagejpeg($image, $dest, 80);
    }

    imagedestroy($image);
}

/**
 * Delete an uploaded file safely
 */
function delete_upload(string $relative_path): void {
    $full_path = rtrim(UPLOAD_PATH, '/') . '/../' . ltrim($relative_path, '/');
    if (file_exists($full_path) && is_file($full_path)) {
        unlink($full_path);
    }
}

/**
 * Handle multiple file uploads (array input)
 * Returns array of relative paths
 */
function handle_multiple_uploads(array $files, string $subdirectory, bool $is_image = true): array {
    $paths = [];
    // Reindex PHP's weird multi-file array
    $file_count = count($files['name']);
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        $single = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
        $paths[] = handle_upload($single, $subdirectory, $is_image);
    }
    return $paths;
}
