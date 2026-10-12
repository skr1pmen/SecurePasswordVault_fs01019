<?php
/**
 * @var array $password
 */
?>
<form action="/main/generation" method="post" class="form">
    <label style="flex-direction: row; gap: 1rem">
        <input type="checkbox" name="uppercase">
        Использовать заглавные буквы
    </label>
    <label style="flex-direction: row; gap: 1rem">
        <input type="checkbox" name="digits">
        Использовать числа
    </label>
    <label style="flex-direction: row; gap: 1rem">
        <input type="checkbox" name="symbol">
        Использовать спец. символы
    </label>
    <label>
        Итоговая длинна пароля
        <input type="number" name="length" min="8" max="32" step="1" value="8">
    </label>
    <button type="submit">Сгенерировать пароли</button>

    <?php if (!empty($password)): ?>
        <ul>
            <?php foreach ($password as $item): ?>
                <li><?= $item ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</form>
