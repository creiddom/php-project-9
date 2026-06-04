<?php

/** @var \App\View\RoutePresenter $route */
/** @var list<array<string, mixed>> $urls */

?>
<h1 class="display-6 mb-4">Сайты</h1>

<table class="table table-bordered table-hover" data-test="urls">
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Имя</th>
            <th scope="col">Дата создания</th>
            <th scope="col">Код ответа</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($urls as $url) : ?>
            <tr>
                <td><?= htmlspecialchars((string) $url['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
                <td>
                    <a
                        href="<?= htmlspecialchars($route->for('urls.show', ['id' => $url['id']]), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                        aria-label="Просмотр сайта <?= htmlspecialchars((string) $url['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                    ><?= htmlspecialchars((string) $url['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></a>
                </td>
                <td><?= htmlspecialchars((string) $url['created_at'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $url['last_status_code'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
