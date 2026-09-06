<?php
// server/config/storage.php

require_once __DIR__ . '/database.php';

/**
 * Helper to upload image assets to Supabase Storage with local fallback.
 * 
 * @param array $fileData Standard $_FILES entry array
 * @return array Response array containing success, url, storage provider, and filename
 */
function uploadFileToStorage(array $fileData): array {
    if (!isset($fileData['tmp_name']) || $fileData['error'] !== UPLOAD_ERR_OK) {
        return [
            "success" => false,
            "error" => "No file uploaded or upload error occurred."
        ];
    }

    $tmpPath = $fileData['tmp_name'];
    $origName = basename($fileData['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION)) ?: 'jpg';
    
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'];
    if (!in_array($ext, $allowedExts)) {
        return [
            "success" => false,
            "error" => "Invalid file format. Allowed formats: " . implode(', ', $allowedExts)
        ];
    }

    $filename = 'product_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
    $mimeType = $fileData['type'] ?? mime_content_type($tmpPath) ?: 'image/' . $ext;

    // Check Supabase Credentials
    $supabaseUrl = rtrim(getenv('SUPABASE_URL') ?: ($_ENV['SUPABASE_URL'] ?? ''), '/');
    $supabaseKey = getenv('SUPABASE_KEY') ?: ($_ENV['SUPABASE_KEY'] ?? '');
    $supabaseBucket = getenv('SUPABASE_BUCKET') ?: ($_ENV['SUPABASE_BUCKET'] ?? 'raj-confections-assets');

    $isSupabaseConfigured = !empty($supabaseUrl) 
        && !empty($supabaseKey) 
        && strpos($supabaseUrl, 'your-project') === false 
        && strpos($supabaseKey, 'your-supabase') === false;

    // 1. Attempt Supabase Upload if credentials are configured
    if ($isSupabaseConfigured) {
        $endpoint = "{$supabaseUrl}/storage/v1/object/{$supabaseBucket}/{$filename}";
        $fileDataBinary = file_get_contents($tmpPath);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileDataBinary);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$supabaseKey}",
            "apikey: {$supabaseKey}",
            "Content-Type: {$mimeType}",
            "x-upsert: true"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            $publicUrl = "{$supabaseUrl}/storage/v1/object/public/{$supabaseBucket}/{$filename}";
            return [
                "success" => true,
                "url" => $publicUrl,
                "storage" => "supabase",
                "filename" => $filename,
                "http_code" => $httpCode
            ];
        } else {
            error_log("Supabase Upload Failed (HTTP {$httpCode}): {$response} | Error: {$curlError}");
            // Fallback to local storage below if Supabase API fails
        }
    }

    // 2. Local File System Fallback
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $targetPath = $uploadDir . $filename;
    if (move_uploaded_file($tmpPath, $targetPath)) {
        return [
            "success" => true,
            "url" => "/uploads/" . $filename,
            "storage" => "local",
            "filename" => $filename
        ];
    }

    return [
        "success" => false,
        "error" => "Failed to save file to storage."
    ];
}
