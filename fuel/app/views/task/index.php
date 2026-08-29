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
                                <a class="reset" href="<?php echo Uri::create('task/edit/'.$task['id']); ?>">編集</a>
                                <form action="<?php echo Uri::create('task/delete'); ?>" method="post">
                                    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                    <button type="submit">🗑</button>
                                </form>
                            </div>
                            <?php if ($task['memo'] !== '') : ?>
                                <div class="t-memo">📝 <?php echo $task['memo']; ?></div>
                            <?php endif; ?>
                            <?php if ($task['total_count'] > 0): ?>
                                <div class="subs">
                                    <?php foreach ($task['subtasks'] as $subtask): ?>
                                        <div class="subrow<?php echo $subtask['done'] ? ' done' : ''; ?>">
                                            <form action="<?php echo Uri::create('task/subtask_toggle'); ?>" method="post">
                                                <input type="hidden" name="id" value="<?php echo $subtask['id']; ?>">
                                                <button class="check" type="submit"><?php echo $subtask['done'] ? '✓' : ''; ?></button>
                                            </form>
                                            <span><?php echo $subtask['title'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="pane form">
        <?php $is_edit = ($form['id'] !== ''); ?>
        <form action="<?php echo Uri::create($is_edit ? 'task/update' : 'task/create'); ?>" method="post">
            <div class="pane-h">
                <span>
                    <?php echo $is_edit ? '✎ タスクを編集' : '＋ 新規タスク登録'; ?>
                    <span class="modebadge<?php echo $is_edit ? ' edit' : ''; ?>"><?php echo $is_edit ? '編集' : '新規'; ?></span>
                </span>
                <?php if ($is_edit): ?>
                    <a class="reset" href="<?php echo Uri::create('task/index'); ?>">✕ キャンセル</a>
                <?php endif; ?>
            </div>

            <input type="hidden" name="id" value="<?php echo $form['id']; ?>">

            <div class="field">
                <label class="lbl">タイトル</label>
                <input class="inp" type="text" name="title" placeholder="例）週末の買い出し" value="<?php echo $form['title']; ?>">
            </div>
            <div class="field row2">
                <div>
                    <label class="lbl">開始日</label>
                    <input class="inp" type="date" name="start_date" value="<?php echo $form['start_date']; ?>">
                </div>
                <div>
                    <label class="lbl">締切</label>
                    <input class="inp" type="date" name="deadline" value="<?php echo $form['deadline']; ?>">
                </div>
            </div>
            <div class="field">
                <label class="lbl">タグ（種別）</label>
                <select class="inp" name="tag_id">
                    <option value="">タグなし</option>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?php echo $tag['id']; ?>"<?php echo $form['tag_id'] == $tag['id'] ? ' selected' : ''; ?>><?php echo $tag['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="lbl">メモ</label>
                <input class="inp" type="text" name="memo" placeholder="補足を入力..." value="<?php echo $form['memo']; ?>">
            </div>
            <button class="submit" type="submit"><?php echo $is_edit ? '更新する' : '登録する'; ?></button>
        </form>
        <?php if ($is_edit): ?>
            <form action="<?php echo Uri::create('task/subtask_create'); ?>" method="post">
                <input type="hidden" name="task_id" value="<?php echo $form['id']; ?>">
                <div class="field">
                    <label class="lbl">サブタスクを追加</label>
                    <input class="inp" type="text" name="title" placeholder="例）牛乳">
                </div>
                <button class="submit" type="submit">＋ 追加する</button>
            </form>
        <?php endif; ?>
    </div>
</div>