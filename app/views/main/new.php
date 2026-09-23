<?php
?>

<form action="/main/new" method="post" class="form">
    <label>
        Введите название сайта
        <input type="text" required name="name">
    </label>
    <label>
        Введите логин
        <input type="text" name="login">
    </label>
    <label>
        Введите пароль
        <input type="password" name="password" required>
    </label>
    <label>
        Введите URL сайта
        <input type="text" name="url">
    </label>
    <label>
        Введите категорию
        <input type="text" name="category">
    </label>
    <label>
        Заметка
        <textarea name="desc"></textarea>
    </label>
    <button type="submit">Создать запись</button>
</form>
