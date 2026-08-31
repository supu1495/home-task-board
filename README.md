# 家庭内タスク掲示板

家族でタスクを共有するための掲示板アプリ。HRクラウド株式会社 開発前課題として作成。

家庭内のタスクを一つの掲示板に集約・可視化し、家族間での抜け漏れや二重作業を防ぐことを目的とする。締切やサブタスクの進捗をひと目で把握でき、完了チェックや絞り込みは画面遷移なしで反映される。

---

## 技術構成

| 項目 | 内容 |
|---|---|
| サーバサイド | PHP 7.3 / FuelPHP 1.8 |
| フロントエンド | Knockout.js 3.5.1 |
| データベース | MySQL 8.0 |
| 実行環境 | Docker（Apache + PHP / MySQL の2コンテナ） |

---

## セットアップ手順

### 1. リポジトリをクローン

```bash
git clone <リポジトリURL>
cd intern_kadai
```

### 2. コンテナを起動

```bash
cd docker
docker-compose build
docker-compose up -d
```

DBコンテナの初回起動時に `docker/db/init/` の以下2ファイルが自動で実行され、テーブル作成と初期データ投入まで完了する。

- `01_schema.sql` … `tag` / `task` / `sub_task` の3テーブルを作成
- `02_seed.sql` … 動作確認用のタグ3件・タスク・サブタスクを投入

データベース名は `intern_kadai_dev`（`docker/docker-compose.yml` と `fuel/app/config/development/db.php` で一致させている）。

### 3. 合言葉を設定する

掲示板は家族共通の合言葉で保護されている。`fuel/app/config/lock.php` にハッシュ化した合言葉が入っている。

**レビュー用の合言葉： `password`**

合言葉を変更する場合は、ハッシュを生成して差し替える。

```bash
docker exec fuelphp-app php -r "echo password_hash('新しい合言葉', PASSWORD_DEFAULT), PHP_EOL;"
```

出力された文字列を `fuel/app/config/lock.php` の `password_hash` に貼り付ける。平文はconfigにもDBにも保持していない。

### 4. ブラウザからアクセス

```
http://localhost/
```

ロック画面が表示されるので、合言葉を入力すると掲示板が使えるようになる。

---

## 機能

### タスク管理
- タスクの登録・一覧・編集・削除（タイトル / 開始日 / 締切 / タグ / メモ）
- 削除は `deleted_at` による論理削除
- 締切が近いタスクは締切表示を強調、完了したタスクは淡色表示
- 登録フォームと編集フォームは右カラムで兼用

### サブタスク管理
- タスクに紐づくサブタスクの追加・完了・削除（`task : sub_task = 1 : n`）
- 達成数／総数は `sub_task` を `COUNT` / `SUM` で集計して算出（集計用のカラムは持たない）

### 完了チェック（非同期）
- タスク／サブタスクの完了状態をページ遷移なしで切り替え
- タスクを完了にすると配下のサブタスクがすべて完了になる
- サブタスクがすべて完了になると親タスクも完了になる（1つ外せば親も未完了に戻る）
- 進捗バーと達成数が即座に更新される

### タグ
- タグによる絞り込み（非同期）
- タグ管理モーダルからタグの追加・名前/色の変更・削除
- タグを削除しても、そのタグが付いていたタスクは「タグなし」として残る（外部キーの `ON DELETE SET NULL`）

### session / cookie
- session … 登録・更新・削除の完了メッセージ（フラッシュ）、認証状態の保持
- cookie … 絞り込みタグの保持（30日）。再訪時に選択状態を復元する

### 簡易認証
- 家族共通の合言葉によるロック
- 合言葉はハッシュ化して config に保持し、`password_verify()` で照合
- `before()` で未認証アクセスをロック画面へリダイレクト
- 「ロックする」ボタンでロック状態に戻せる

### セキュリティ
- CSRFトークンの発行・検証（同期フォーム・非同期リクエストの両方）
- 入力値検証と、エラー時の入力内容を保持したままの差し戻し
- 出力時のXSSエスケープ
- DBアクセスはすべてDBクラスのクエリビルダ経由

---

## データベース設計

テーブル作成用のSQLはリポジトリに含まれている。

| ファイル | 内容 |
|---|---|
| `docker/db/init/01_schema.sql` | `tag` / `task` / `sub_task` のCREATE TABLE文 |
| `docker/db/init/02_seed.sql` | 動作確認用の初期データ |

どちらもDBコンテナの初回起動時に自動実行されるため、手動で流す必要はない。既存のDBに対して実行する場合は次のコマンドを使う。

```bash
docker exec -i docker-db-1 mysql -uroot -proot intern_kadai_dev < docker/db/init/01_schema.sql
```

### リレーション

```
tag (1) ── (n) task (1) ── (n) sub_task
```

- `task.tag_id` → `tag.id`（`ON DELETE SET NULL`）
- `sub_task.task_id` → `task.id`

### tag

| カラム | 型 | NULL | 備考 |
|---|---|---|---|
| id | int | NO | 主キー / AUTO_INCREMENT |
| name | varchar(20) | NO | タグ名 |
| color | varchar(7) | YES | 表示色 |
| created_at | timestamp | YES | |

### task

| カラム | 型 | NULL | 備考 |
|---|---|---|---|
| id | int | NO | 主キー / AUTO_INCREMENT |
| title | varchar(50) | NO | タスク名 |
| start_date | date | YES | 開始日 |
| deadline | date | YES | 締切日 |
| tag_id | int | YES | 外部キー → tag.id |
| memo | varchar(255) | YES | メモ |
| done | tinyint(1) | NO | 0:未達成 1:達成 |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | ON UPDATE CURRENT_TIMESTAMP |
| deleted_at | timestamp | YES | 論理削除。NULL以外は画面に表示しない |

### sub_task

| カラム | 型 | NULL | 備考 |
|---|---|---|---|
| id | int | NO | 主キー / AUTO_INCREMENT |
| task_id | int | NO | 外部キー → task.id |
| title | varchar(50) | NO | サブタスク名 |
| done | tinyint(1) | NO | 0:未達成 1:達成 |
| created_at | timestamp | YES | |
| updated_at | timestamp | YES | ON UPDATE CURRENT_TIMESTAMP |
| deleted_at | timestamp | YES | 論理削除 |

進捗（例 `2 / 4`）は集計用のカラムを持たず、都度クエリで算出している。

- 総数：`SELECT COUNT(*) FROM sub_task WHERE task_id = ? AND deleted_at IS NULL`
- 達成数：上記に `AND done = 1` を追加

---

## ディレクトリ構成

```
fuel/app/
  classes/
    controller/
      task.php        掲示板（一覧・登録・更新・削除・非同期アクション）
      lock.php        ロック画面・認証・ログアウト
    model/
      task.php        task テーブルへのアクセス
      subtask.php     sub_task テーブルへのアクセス
      tag.php         tag テーブルへのアクセス
  config/
    development/db.php  DB接続設定
    lock.php            合言葉のハッシュ
    config.php          cookie / セキュリティ設定
  views/
    template.php      全ページ共通の外枠
    task/index.php    掲示板
    lock/index.php    ロック画面
public/assets/
  css/board.css       画面スタイル
  js/board.js         Knockout.js の ViewModel
docker/
  db/init/            起動時に実行されるSQL
```

---

## 課題条件との対応

| # | 条件 | 実装箇所 |
|---|---|---|
| 1 | PHP / FuelPHP | 全体 |
| 2 | beforeメソッド | `Controller_Task::before()`（認証チェック・CSRF検証）、`Controller_Lock::before()` |
| 3 | configのカスタマイズ | `development/db.php`（DB接続）、`lock.php`（合言葉）、`config.php`（cookie有効期限・HttpOnly・出力エスケープ） |
| 4 | session / cookie | session:フラッシュメッセージ・認証状態 / cookie:絞り込みタグの保持 |
| 5 | ネームスペース | `Model\Task` / `Model\SubTask` / `Model\Tag` |
| 6 | `\` によるグローバル名前空間アクセス | Model内の `\DB`、Controller内の `\Model\Task` |
| 7 | DBクラス | 全SQLを `\DB::select()` 等のクエリビルダで記述。Controllerからは直接DBを呼ばない |
| 8 | 1:n構造・正規化 | `tag - task`、`task - sub_task`。進捗は集計カラムを持たずCOUNTで算出 |
| 9 | CRUD網羅 | task / sub_task / tag の3テーブルすべて |
| 10 | Knockout.js | 一覧表示、完了チェック、絞り込み、タグ管理モーダル |
| 11 | 非同期UI | 完了チェック、進捗の連動、絞り込み、タグCRUD |
| 12 | GitHub管理 | 1 issue = 1ブランチ = 1PR で運用 |
| 13 | セキュリティ | CSRF / 入力値検証 / XSS / SQLインジェクション / 合言葉ロック |

---

## 開発者向け情報

### ログ

FuelPHPのエラーログは日付ごとに出力される。

```bash
tail -20 fuel/app/logs/$(date +%Y/%m/%d).php
```

### 構文チェック

```bash
docker exec fuelphp-app php -l /var/www/html/my_fuel_project/fuel/app/classes/controller/task.php
node --check public/assets/js/board.js
```

### DBへの接続

```bash
docker exec -it docker-db-1 mysql -uroot -proot intern_kadai_dev
```

| 項目 | 値 |
|---|---|
| ホスト | localhost（コンテナ間では `db`） |
| ポート | 3306 |
| ユーザー | root |
| パスワード | root |
| データベース名 | intern_kadai_dev |

### 対話シェル

```bash
docker exec -it fuelphp-app php /var/www/html/my_fuel_project/oil console
```

---

## 実装範囲外

- 締切・完了状況によるタスクの並び替え（工数の都合により見送り）
- ユーザーごとのログイン・アカウント管理（今回は家族共通の合言葉による簡易認証のみ）
