<div class="lockwrap">
    <form class="lockbox" action="<?php echo Uri::create('lock/unlock'); ?>" method="post">
        <h2>🔒 合言葉を入力してください</h2>
        <p class="lockmsg">家族で共有している合言葉を入力すると、掲示板を利用できます。</p>
        <?php if ($error): ?>
            <div class="lockerr"><?php echo $error; ?></div>
        <?php endif; ?>
        <input class="inp" type="password" name="password" autofocus>
        <button class="submit" type="submit">ロックを解除する</button>
    </form>
</div>