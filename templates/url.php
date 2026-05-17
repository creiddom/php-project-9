<h1 class="display-6 mb-4">Сайт: <?= htmlspecialchars((string) $url['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></h1>

<table class="table table-bordered" data-test="url">
    <tbody>
        <tr>
            <td>ID</td>
            <td><?= htmlspecialchars((string) $url['id'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Имя</td>
            <td><?= htmlspecialchars((string) $url['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
        </tr>
        <tr>
            <td>Дата создания</td>
            <td><?= htmlspecialchars((string) $url['created_at'], ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></td>
        </tr>
    </tbody>
</table>

<h2 class="display-6 mt-5 mb-3">Проверки</h2>

<table class="table table-bordered" data-test="checks">
    <thead>
        <tr>
            <th>ID</th>
            <th>Код ответа</th>
            <th>h1</th>
            <th>title</th>
            <th>description</th>
            <th>Дата создания</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
