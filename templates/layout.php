<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string)($title ?? 'Приложение'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid d-flex flex-wrap align-items-center">
        <a class="navbar-brand mb-0" href="/">Анализатор страниц</a>
        <a class="nav-link mb-0 ms-3 link-light link-opacity-75" href="/urls">Сайты</a>
    </div>
</nav>
<main class="flex-grow-1">
    <div class="container-lg mt-3">
        <?php foreach ($flash ?? [] as $message) : ?>
            <?php
            $type = htmlspecialchars((string)($message['type'] ?? 'info'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = htmlspecialchars((string)($message['text'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            ?>
            <div class="alert alert-<?= $type ?>" role="alert"><?= $text ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </div>
</main>
<hr class="border-secondary-subtle mb-0">
<footer class="py-3">
    <div class="container-lg text-center">
        <a href="https://ru.hexlet.io" class="link-primary text-decoration-none">Hexlet</a>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
