<!doctype html>
 <html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>家庭内タスク掲示板</title>
        <?php echo Asset::css('board.css'); ?>
    </head>
    <body>
        <div class="wrap">
            <div class="appbar">
                <div class="brand">家庭内タスク掲示板</div>
                <?php if (Session::get('authenticated')): ?>
                <form action="<?php echo Uri::create('lock/logout'); ?>" method="post">
                    <button class="lockbtn" type="submit">🔓 ロックする</button>
                </form>
                <?php endif; ?>
            </div>
            <?php echo $content; ?>
        </div>
        <?php echo Asset::js('knockout-3.5.1.js'); ?>
        <?php echo Asset::js('board.js'); ?>
    </body>
 </html>