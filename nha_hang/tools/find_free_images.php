<?php
// Script: find_free_images.php
// Usage: php find_free_images.php
// Finds menu_items without images and queries Wikimedia Commons for candidate free images.

require_once __DIR__ . '/../config/config.php';

$db = getDB();

$rows = $db->query("SELECT id, name FROM menu_items WHERE image IS NULL OR image = ''")->fetchAll();
if (!$rows) {
    echo "No missing images found.\n";
    exit;
}

$results = [];

foreach ($rows as $r) {
    $name = $r['name'];
    $id = $r['id'];
    echo "Searching for: $name\n";

    // Wikimedia Commons search in File namespace (6)
    $query = rawurlencode($name);
    $url = "https://commons.wikimedia.org/w/api.php?action=query&list=search&srsearch={$query}&srnamespace=6&srlimit=5&format=json";
    $resp = @file_get_contents($url);
    if (!$resp) {
        echo "  Warning: no response for $name\n";
        continue;
    }
    $json = json_decode($resp, true);
    $candidates = [];
    if (!empty($json['query']['search'])) {
        foreach ($json['query']['search'] as $hit) {
            $title = $hit['title']; // e.g., File:Pho bo.jpg
            // Get imageinfo with extmetadata
            $title_enc = rawurlencode($title);
            $infoUrl = "https://commons.wikimedia.org/w/api.php?action=query&titles={$title_enc}&prop=imageinfo&iiprop=url|extmetadata&format=json";
            $infoResp = @file_get_contents($infoUrl);
            if (!$infoResp) continue;
            $infoJson = json_decode($infoResp, true);
            if (empty($infoJson['query']['pages'])) continue;
            foreach ($infoJson['query']['pages'] as $p) {
                if (empty($p['imageinfo'][0])) continue;
                $ii = $p['imageinfo'][0];
                $imgUrl = $ii['url'] ?? null;
                $ext = $ii['extmetadata'] ?? [];
                $license = $ext['LicenseShortName']['value'] ?? ($ext['LicenseUrl']['value'] ?? '');
                $artist = $ext['Artist']['value'] ?? '';
                $credit = $ext['Credit']['value'] ?? '';
                $candidates[] = [
                    'title' => $title,
                    'image_url' => $imgUrl,
                    'license' => $license,
                    'artist' => strip_tags($artist),
                    'credit' => strip_tags($credit)
                ];
                if (count($candidates) >= 3) break;
            }
            if (count($candidates) >= 3) break;
        }
    }

    $results[$id] = [
        'id' => $id,
        'name' => $name,
        'candidates' => $candidates
    ];
    // be polite
    sleep(1);
}

$outFile = __DIR__ . '/image_candidates.json';
file_put_contents($outFile, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Saved candidates to: $outFile\n";
