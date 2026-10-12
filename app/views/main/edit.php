<?php
/**
 * @var array $data;
 */
?>

<form action="/main/edit?id=<?= $data['id'] ?>" method="post" class="form">
    <label>
        Введите название сайта
        <input type="text" required name="name" value="<?= $data['title'] ?>">
    </label>
    <label>
        Введите логин
        <input type="text" name="login" value="<?= $data['username'] ?>">
    </label>
    <label>
        Введите пароль
        <input type="password" name="password" required value="<?= $data['password'] ?>">
    </label>
    <label>
        Введите URL сайта
        <input type="text" name="url" value="<?= $data['url'] ?>">
    </label>
    <label>
        Введите категорию
        <input type="text" name="category" value="<?= $data['category'] ?>">
    </label>
    <label>
        Заметка
        <textarea name="desc"><?= $data['notes'] ?></textarea>
    </label>
    <button type="submit">Редактировать запись</button>
</form>
