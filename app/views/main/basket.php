<?php
/**
 * @var array $data
 */
?>
<?php if (!empty($data)) : ?>
<ul>
    <?php foreach ($data as $item) : ?>
        <li>
            <?= $item['title'] ?>
            <a href="/main/basket?recovery=<?= $item['id'] ?>">Восстановить</a>
            <a href="/main/basket?delete=<?= $item['id'] ?>">Удалить окончательно</a>
        </li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<h2>Ваша корзина пуста</h2>
<?php endif; ?>