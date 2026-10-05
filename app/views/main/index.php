<?php
/**
 *
 * @var string $countPass;
 * @var array $passwords;
 *
 */

?>


<a href="/main/new">Создать новую запись</a>
<a href="/main/generation">Сгенерировать пароль</a>

<div>Всего записей: <?= empty($countPass) ? "0" : $countPass ?></div>
<ul>
    <?php if (!empty($passwords)): ?>
        <?php foreach ($passwords as $pass): ?>
            <li>
                <a href="/main/password?id=<?= $pass['id'] ?>">
                    <?= $pass['title'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <h2>Записей пока нет</h2>
    <?php endif; ?>
</ul>