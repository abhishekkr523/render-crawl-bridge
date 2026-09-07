<?php
$secret = getenv('BRIDGE_SECRET') ?: 'MY_SECRET_BRIDGE_KEY_123';
$dataFile = __DIR__ . '/urls.json';

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

// POST: नया URL जोड़ने के लिए
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $url = filter_var($_POST['url'] ?? '', FILTER_VALIDATE_URL);

    if ($token !== $secret) {
        http_response_code(403);
        exit('Forbidden');
    }

    if (!$url) {
        http_response_code(400);
        exit('Invalid URL');
    }

    $urls = json_decode(file_get_contents($dataFile), true) ?: [];
    if (!in_array($url, $urls)) {
        array_unshift($urls, $url); // सबसे ऊपर जोड़ें
        $urls = array_slice($urls, 0, 500); // अधिकतम 500 लिंक्स रखें
        file_put_contents($dataFile, json_encode($urls, JSON_PRETTY_PRINT));
    }

    echo "OK";
    exit;
}

// GET: Googlebot और यूज़र्स को लिंक्स दिखाने के लिए
$urls = json_decode(file_get_contents($dataFile), true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow">
    <title>Discovery Feed</title>
    <meta name="google-site-verification" content="ihhndB8eumDzU7laPZy3ounA7bD6k-NfcTohxNj85dw" />
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2>Network Discovery Feed</h2>
    <p>Automated crawl pass for registered outgoing endpoints:</p>
    <ul>
        <?php foreach ($urls as $link): ?>
            <li><a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" rel="follow"><?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?></a></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
