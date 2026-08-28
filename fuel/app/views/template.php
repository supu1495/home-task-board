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
            </div>
            <?php echo $content; ?>
        </div>
    </body>
 </html>