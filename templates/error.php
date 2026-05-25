<?php

/** @var \App\View\RoutePresenter $route */

$statusCode = (int) ($statusCode ?? 500);
$title = (string) ($title ?? 'Ошибка');
$message = (string) ($message ?? '');
$details = (string) ($details ?? '');

?>
<div class="row">
    <div class="col-12 col-md-8 mx-auto text-center py-5">
        <h1 class="display-4 mb-3"><?= $statusCode ?> — <?= htmlspecialchars($title, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h1>
        <p class="lead mb-4"><?= htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></p>
        <?php if ($details !== '') : ?>
            <pre class="text-start text-muted small bg-light p-3 rounded mb-4"><?= htmlspecialchars($details, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></pre>
        <?php endif; ?>
        <a href="<?= htmlspecialchars($route->for('home'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" class="btn btn-primary">На главную</a>
    </div>
</div>
