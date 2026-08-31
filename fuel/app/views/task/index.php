<script>
  var initialTasks = <?php echo $tasks_json; ?>;
  var endpoints = {
    toggleTask: '<?php echo Uri::create('task/toggle'); ?>',
    toggleSubtask: '<?php echo Uri::create('task/subtask_toggle'); ?>'
  };
</script>
<div class="grid">
    <div class="pane"><!-- 左の箱 -->
        <div class="pane-h"><!-- タスク一覧 -->
            タスク一覧(<?php echo count($tasks); ?>件)
        </div>
        <div class="board" id="board"><!-- カードを並べる箱 -->
            <div data-bind="foreach: tasks">
                <div class="task" data-bind="css: { done: done }, style: { borderLeftColor: tag_color }">
                    <div class="t-top">
                        <div class="check" data-bind="click: $root.toggleTask, text: done() ? '✓' : '', style: { background: done() ? tag_color : '', borderColor: done() ? tag_color : '' }"></div>
                        <div class="t-body">
                            <div class="t-titlerow">
                                <span class="t-title" data-bind="text: title"></span>
                                <span class="tag" data-bind="text: tag_name, style: { background: tag_color }"></span>
                                <span class="due" data-bind="text: '締切' + deadline, css: { soon: soon }"></span>
                                <a class="reset" data-bind="attr: { href: '<?php echo Uri::create('task/edit'); ?>/' + id }">編集</a>
                                <form action="<?php echo Uri::create('task/delete'); ?>" method="post">
                                    <input type="hidden" name="id" data-bind="attr: { value: id }">
                                    <button type="submit">🗑</button>
                                </form>
                            </div>
                            <div class="t-memo" data-bind="visible: memo, text: '📝 ' + memo"></div>
                            <div class="prog" data-bind="visible: total_count > 0">
                                <div class="bar"><span data-bind="style: { width: percent + '%', background: tag_color }"></span></div>
                                <span class="pcount" data-bind="text: done_count + ' / ' + total_count"></span>
                            </div>
                            <div class="subs" data-bind="foreach: subtasks">
                                <div class="subrow" data-bind="css: { done: done }">
                                    <div class="check" data-bind="click: $root.toggleSubtask, text: done() ? '✓' : ''"></div>
                                    <span data-bind="text: title"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            <div class="pane-h">サブタスク</div>
                <?php foreach ($form['subtasks'] as $subtask): ?>
                    <form action="<?php echo Uri::create('task/subtask_delete'); ?>" method="post">
                        <input type="hidden" name="id" value="<?php echo $subtask['id']; ?>">
                        <input type="hidden" name="task_id" value="<?php echo $form['id'] ?>">
                        <span><?php echo $subtask['title'] ?></span>
                        <button type="submit">🗑</button>
                    </form>
                <?php endforeach; ?>
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