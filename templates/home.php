<?php

$errors = $errors ?? [];
$urlName = $urlName ?? '';

?>
<div class="row">
    <div class="col-12 col-md-10 col-lg-8 mx-auto border rounded-3 bg-light p-5">
        <h1 class="display-3">Анализатор страниц</h1>
        <p class="lead">Бесплатно проверяйте сайты на SEO-пригодность</p>
        <form action="/urls" method="post" class="row">
            <div class="col-8">
                <label for="url" class="visually-hidden">Url для проверки</label>
                <input
                    class="form-control form-control-lg<?= $errors !== [] ? ' is-invalid' : '' ?>"
                    type="text"
                    id="url"
                    name="url"
                    value="<?= htmlspecialchars((string) $urlName, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                    placeholder="https://www.example.com"
                >
                <?php foreach ($errors as $error): ?>
                    <div class="invalid-feedback d-block"><?= htmlspecialchars((string) $error, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
            <div class="col-2">
                <input class="btn btn-primary btn-lg ms-3 px-5 text-uppercase mx-3" type="submit" value="Проверить">
            </div>
        </form>
    </div>
</div>
