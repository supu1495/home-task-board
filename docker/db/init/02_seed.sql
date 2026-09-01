INSERT INTO tag (id, name, color) VALUES (1, '買い出し', '#4caf50');
INSERT INTO tag (id, name, color) VALUES (2, '家事', '#2e31db');
INSERT INTO tag (id, name, color) VALUES (3, '伝言', '#ffbe30');

INSERT INTO task (id, title, deadline, tag_id, memo, done) VALUES (1, '週末の買い出し', '2028-11-29', 1, NULL, 0);
INSERT INTO task (id, title, deadline, tag_id, memo, done) VALUES (2, 'リビングの片付け', '2026-08-31', 2, NULL, 0);
INSERT INTO task (id, title, start_date, deadline, tag_id, memo, done) VALUES (3, '祖母に電話する', '2025-12-29', '2026-08-30', 3, '規則を守る', 0);
INSERT INTO task (id, title, deadline, tag_id, memo, done, deleted_at) VALUES (4, '燃えるゴミ出し', '2026-01-01', 2, NULL, 1, '2026-08-20 10:30:00');
INSERT INTO task (id, title, deadline, tag_id, memo, done) VALUES (5, '燃えないゴミ出し', '2026-01-01', 2, NULL, 1);

INSERT INTO sub_task (id, task_id, title, done) VALUES (1, 1, '牛乳', 1);
INSERT INTO sub_task (id, task_id, title, done) VALUES (2, 1, '米', 0);
INSERT INTO sub_task (id, task_id, title, done) VALUES (3, 1, 'トイレットペーパー', 0);
INSERT INTO sub_task (id, task_id, title, done) VALUES (4, 2, '掃除機をかける', 1);
INSERT INTO sub_task (id, task_id, title, done) VALUES (5, 2, '窓を拭く', 0);
INSERT INTO sub_task (id, task_id, title, done) VALUES (6, 5, '捨てるものの分別', 1);
INSERT INTO sub_task (id, task_id, title, done) VALUES (7, 5, '昨日のゴミが燃えないゴミか問い合わせる', 1);