<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

try {
    $client = new Client();
    $client->setAuthConfig(__DIR__ . '/../config/quickpick-drive-key.json');
    $client->addScope(Drive::DRIVE);

    $service = new Drive($client);

    // ✅ Add this for Shared Drive support
    $driveOptions = ['supportsAllDrives' => true];

    // 🔹 Replace with your actual folder ID in the Shared Drive
    $folderId = '1u-jS02bOBfGFP0dsi0sL_QS-wVlDhdl-';

    // Verify the folder exists and is visible
    $folder = $service->files->get($folderId, [
        'fields' => 'id, name',
        'supportsAllDrives' => true
    ]);

    echo "📂 Found folder: " . $folder->getName() . "<br>";

    $fileMetadata = new Drive\DriveFile([
        'name' => 'test_upload.txt',
        'parents' => [$folderId]
    ]);

    $content = "Hello from QuickPick Google Drive integration test!";
    $uploadedFile = $service->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => 'text/plain',
        'uploadType' => 'multipart',
        'fields' => 'id',
        'supportsAllDrives' => true
    ]);

    echo "✅ Uploaded successfully! File ID: " . $uploadedFile->id;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
