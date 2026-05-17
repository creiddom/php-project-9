<h1 class="display-6 mb-4">Сайты</h1>

<table class="table table-bordered table-hover" data-test="urls">
    <thead>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Дата создания</th>
            <th>Код ответа</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($urls as $url): ?>
            <tr>
                <td><?= htmlspecialchars((string) $url['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
                <td>
                    <a href="/urls/<?= htmlspecialchars((string) $url['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                        <?= htmlspecialchars((string) $url['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
                    </a>
                </td>
                <td><?= htmlspecialchars((string) $url['created_at'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
