<?php
/**
 * @var array $userData
 * @var array $userLogs
 */
?>

<div class="container">
    <h1><?= $userData['login'] ?></h1>
    <p><?= $userData['created_at'] ?></p>
    <hr>
    <ul>
        <?php foreach ($userLogs as $log): ?>
            <li>
                <?= $log['id'] ?> |
                <?= $log['type'] ?> |
                <?= $log['ip_address'] ?> |
                <?= $log['details'] ?> |
                <?= $log['created_at'] ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
