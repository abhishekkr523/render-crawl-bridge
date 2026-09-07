<?php
// Service Account verification endpoint
if ($_SERVER['REQUEST_URI'] === '/google-site-verification.html') {
    header('Content-Type: text/html');
    echo 'google-site-verification: google-site-verification.html';
    exit;
}

$secret = getenv('BRIDGE_SECRET') ?: 'MY_SECRET_BRIDGE_KEY_123';
$dataFile = '/tmp/urls.json';
$secret = getenv('BRIDGE_SECRET') ?: 'MY_SECRET_BRIDGE_KEY_123';
$dataFile = __DIR__ . '/urls.json';

// फ़ाइल न हो तो खाली ऐरे बनाएँ
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

// POST: नया लिंक जोड़ने के लिए
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $url = trim($_POST['url'] ?? '');

    if ($token !== $secret) {
        http_response_code(403);
        exit('Forbidden');
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        exit('Invalid URL');
    }

    $urls = json_decode(@file_get_contents($dataFile), true);
    if (!is_array($urls)) {
        $urls = [];
    }

    // अगर लिंक पहले से नहीं है, तो सबसे ऊपर जोड़ें
    if (!in_array($url, $urls)) {
        array_unshift($urls, $url);
        file_put_contents($dataFile, json_encode($urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    echo "OK";
    exit;
}

// GET: लिंक्स दिखाने के लिए
$urls = json_decode(@file_get_contents($dataFile), true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow">
    <title>Priority Discovery Feed</title>
        <meta name="google-site-verification" content="ihhndB8eumDzU7laPZy3ounA7bD6k-NfcTohxNj85dw" />
</head>
<body style="font-family: sans-serif; padding: 24px; max-width: 800px; margin: auto;">
    <h2>Network Discovery Feed</h2>
    <p>Automated crawl pass for registered outgoing endpoints:</p>
    <ul>
        <?php if (empty($urls)): ?>
            <li>No submissions registered yet.</li>
        <?php else: ?>
            <?php foreach ($urls as $link): ?>
                <li style="margin-bottom: 8px;">
                    <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" rel="follow" target="_blank">
                        <?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</body>
</html>
