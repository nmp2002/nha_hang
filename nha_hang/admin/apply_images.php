<?php
$pageTitle = 'Áp dụng ảnh cho món';
require_once '../config/config.php';

if (!isAdmin() && !isStaff()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['choice']) || !is_array($_POST['choice'])) {
    redirect('image_candidates.php');
}

$choices = $_POST['choice'];
$db = getDB();
$saved = 0;

foreach ($choices as $itemId => $url) {
    $itemId = (int)$itemId;
    $url = trim($url);
    if (empty($url)) continue;

    // Allow only Wikimedia hosts for safety
    $allowedHosts = ['upload.wikimedia.org', 'commons.wikimedia.org', 'static.wikimedia.org'];
    $host = parse_url($url, PHP_URL_HOST);
    if (!in_array($host, $allowedHosts)) {
        continue;
    }

    // Download image
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower(trim($db->quote((string)$itemId), "'")));
    $filename = 'menu_' . $itemId . '_' . time() . '.' . $ext;
    $dest = __DIR__ . '/../assets/images/' . $filename;

    // Use curl to fetch
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $data = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($data === false || !$data) continue;

    file_put_contents($dest, $data);

    // Update DB with relative path
    $relPath = 'assets/images/' . $filename;
    $stmt = $db->prepare('UPDATE menu_items SET image = ? WHERE id = ?');
    if ($stmt->execute([$relPath, $itemId])) {
        $saved++;
    }
}

// Redirect back with message
$_SESSION['flash'] = "$saved ảnh được cập nhật.";
redirect('image_candidates.php');
