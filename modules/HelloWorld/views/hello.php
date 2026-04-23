<?php
use App\Modules\HelloWorld\Models\HelloWorld;

$model = new HelloWorld();
$mainMessage = htmlspecialchars($model->getMessage());
$randomMessage = htmlspecialchars($model->getMessage(true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World - StrataPHP</title>
    <link rel="stylesheet" href="/modules/helloworld/assets/style.css">
</head>
<body>
    <h1><?= $mainMessage ?></h1>
    <p>This is a simple demonstration of a StrataPHP module.</p>
    <p><strong>Random message:</strong> <?= $randomMessage ?></p>
    <p><a href="/">← Back to Home</a> | <a href="/hello">🔄 Refresh for random message</a></p>
</body>
</html>
