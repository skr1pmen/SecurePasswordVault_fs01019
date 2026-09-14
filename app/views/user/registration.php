<?php ?>

<form class="form" action="/user/registration" method="post">
    <label>
        Ваш логин
        <input type="text" name="login">
    </label>
    <label>
        Ваш пароль
        <input type="password" name="password">
    </label>
    <label>
        Повторите ваш пароль
        <input type="password" name="rPassword">
    </label>
    <button type="submit">Зарегистрироваться</button>
</form>
