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
    <div class="pane">
        登録フォーム（準備中）
    </div>
</div>