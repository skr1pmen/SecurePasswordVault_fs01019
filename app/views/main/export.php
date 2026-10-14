<?php ?>
<form action="/main/export" method="post" class="form">
    <label style="flex-direction: row; gap: 1rem">
        <input type="radio" name="type" value="json" checked>
        JSON
    </label>
    <label style="flex-direction: row; gap: 1rem">
        <input type="radio" name="type" value="csv">
        CSV
    </label>
    <label>
        Введите ваш пароль
        <input type="password" name="password">
    </label>
    <button type="submit">Экспортировать</button>
</form>

