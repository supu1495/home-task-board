<div class="grid">
    <div class="pane"><!-- 左の箱 -->
        <div class="pane-h"><!-- タスク一覧 -->
            タスク一覧(<?php echo count($tasks); ?>件)
        </div>
        <div class="board"><!-- カードを並べる箱 -->
            <?php foreach ($tasks as $task): ?>
                <div class="task<?php echo $task['done'] ? ' done' : ''; ?>" style="border-left-color: <?php echo $task['tag_color']; ?>">
                    <div class="t-top">
                        <div class="check"></div>
                        <div class="t-body">
                            <div class="t-titlerow">
                                <span class="t-title"><?php echo $task['title']; ?></span>
                                <span class="tag" style="background: <?php echo $task['tag_color']; ?>"><?php echo $task['tag_name']; ?></span>
                                <span class="due<?php echo $task['soon'] ? ' soon' : ''; ?>"> 締切<?php echo $task['deadline']; ?></span>
                            </div>
                            <?php if ($task['memo'] !== '') : ?>
                                <div class="t-memo">📝 <?php echo $task['memo']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="pane form">
        <form action="<?php echo Uri::create('task/create'); ?>" method="post">
            <div class="pane-h">
                <span>＋ 新規タスク登録 <span class="modebadge">新規</span></span>
            </div>

            <div class="field">
                <label class="lbl">タイトル</label>
                <input class="inp" type="text" name="title" placeholder="例）週末の買い出し">
            </div>
            <div class="field row2">
                <div>
                    <label class="lbl">開始日</label>
                    <input class="inp" type="date" name="start_date">
                </div>
                <div>
                    <label class="lbl">締切</label>
                    <input class="inp" type="date" name="deadline">
                </div>
            </div>
            <div class="field">
                <label class="lbl">タグ（種別）</label>
                <select class="inp" name="tag_id">
                    <option value="">タグなし</option>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?php echo $tag['id']; ?>"><?php echo $tag['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="lbl">メモ</label>
                <input class="inp" type="text" name="memo" placeholder="補足を入力...">
            </div>
            <button class="submit" type="submit">登録する</button>
        </form>
    </div>
</div>