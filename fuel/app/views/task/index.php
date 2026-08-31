<?php
$csrf_key = Config::get('security.csrf_token_key');
$csrf_token = Security::fetch_token();
?>
<script>
  var initialTasks = <?php echo $tasks_json; ?>;
  var initialTags = <?php echo $tags_json; ?>;
  var initialFilterTagId = <?php echo $filter_tag_id === '' ? 'null' : (int) $filter_tag_id; ?>;
  var endpoints = {
    toggleTask: '<?php echo Uri::create('task/toggle'); ?>',
    toggleSubtask: '<?php echo Uri::create('task/subtask_toggle'); ?>',
    tagCreate: '<?php echo Uri::create('task/tag_create'); ?>',
    tagUpdate: '<?php echo Uri::create('task/tag_update'); ?>',
    tagDelete: '<?php echo Uri::create('task/tag_delete'); ?>',
    filter: '<?php echo Uri::create('task/filter'); ?>'
  };
  var csrfKey = '<?php echo $csrf_key; ?>';
  var csrfToken = '<?php echo $csrf_token; ?>';
</script>
<div id="app">
    <div class="filters">
        <span class="chip" data-bind="click: selectAll, css: { on: selectedTagId() === null }">すべて</span>
        <!-- ko foreach: tags -->
        <span class="chip" data-bind="click: $root.selectTag, css: { on: $root.selectedTagId() === id }">
            <span class="dot" data-bind="style: { background: color }"></span>
            <span data-bind="text: name"></span>
        </span>
        <!-- /ko -->
        <span class="chip manage" data-bind="click: openTagModal">⚙ タグ管理</span>
        <?php if ($flash): ?>
            <div class="flash show">✓ <?php echo $flash; ?></div>
        <?php endif; ?>
    </div>
    <div class="grid">
    <div class="pane"><!-- 左の箱 -->
        <div class="pane-h"><!-- タスク一覧 -->
            <span data-bind="text: 'タスク一覧(' + filteredTasks().length + '件)'"></span>
        </div>
        <div class="board" id="board"><!-- カードを並べる箱 -->
            <div data-bind="foreach: filteredTasks">
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
                                    <input type="hidden" name="<?php echo $csrf_key; ?>" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="id" data-bind="attr: { value: id }">
                                    <button type="submit">🗑</button>
                                </form>
                            </div>
                            <div class="t-memo" data-bind="visible: memo, text: '📝 ' + memo"></div>
                            <div class="prog" data-bind="visible: total_count() > 0">
                                <div class="bar"><span data-bind="style: { width: percent() + '%', background: tag_color }"></span></div>
                                <span class="pcount" data-bind="text: done_count() + ' / ' + total_count()"></span>
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
            <input type="hidden" name="<?php echo $csrf_key; ?>" value="<?php echo $csrf_token; ?>">
            <div class="pane-h">
                <span>
                    <?php echo $is_edit ? '✎ タスクを編集' : '＋ 新規タスク登録'; ?>
                    <span class="modebadge<?php echo $is_edit ? ' edit' : ''; ?>"><?php echo $is_edit ? '編集' : '新規'; ?></span>
                </span>
                <?php if ($is_edit): ?>
                    <a class="reset" href="<?php echo Uri::create('task/index'); ?>">✕ キャンセル</a>
                <?php endif; ?>
            </div>
            <?php if ($errors): ?>
                <div class="errbox">
                    <?php foreach ($errors as $error): ?>
                        <div>・<?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
                        <input type="hidden" name="<?php echo $csrf_key; ?>" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="id" value="<?php echo $subtask['id']; ?>">
                        <input type="hidden" name="task_id" value="<?php echo $form['id'] ?>">
                        <span><?php echo $subtask['title'] ?></span>
                        <button type="submit">🗑</button>
                    </form>
                <?php endforeach; ?>
            <form action="<?php echo Uri::create('task/subtask_create'); ?>" method="post">
                <input type="hidden" name="<?php echo $csrf_key; ?>" value="<?php echo $csrf_token; ?>">
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
    <div class="overlay" data-bind="css: { show: isTagModalOpen }">
        <div class="modal">
            <h3>タグ管理</h3>
            <div class="msub">名前と色を変更できます。削除してもタスクは残り、タグなしになります。</div>
            <!-- ko foreach: tags -->
            <div class="tagrow">
                <input type="color" data-bind="value: color, event: { change: $root.updateTag }">
                <input type="text" data-bind="value: name, event: { change: $root.updateTag }">
                <span class="del" data-bind="click: $root.deleteTag">🗑</span>
            </div>
            <!-- /ko -->
            <div class="addtag">
                <input type="color" data-bind="value: newTagColor">
                <input type="text" placeholder="新しいタグ名" data-bind="value: newTagName">
                <button type="button" data-bind="click: createTag">追加</button>
            </div>
            <div class="modal-close">
                <button type="button" data-bind="click: closeTagModal">閉じる</button>
            </div>
        </div>
    </div>
</div>