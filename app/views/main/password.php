<?php
/**
 *
 * @var array $data;
 *
 */
?>

<a href="/main/edit?id=<?= $data['id'] ?>">Редактирование</a>
<a href="/main/password?id=<?= $data['id'] ?>&show=true">Просмотр пароля</a>
<a href="/main/delete?id=<?= $data['id'] ?>">Удаление</a>
<ul>
    <?php foreach ($data as $info): ?>
        <li><?= $info ?></li>
    <?php endforeach; ?>
</ul>
