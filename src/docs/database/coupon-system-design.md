# クーポンシステム データベース設計書

## 概要

飲食店の空席時間を活用するためのクーポンシステムのデータベース設計書です。

### 主な機能
- 店舗管理者によるクーポンの作成・管理
- スポット発行（即時発行）とスケジュール発行（定期発行）
- 位置情報ベースのクーポン配信
- 取得人数制限機能
- リアルタイム通知対応

### 技術仕様
- **フレームワーク**: Laravel 10.x
- **データベース**: MySQL 8.0
- **ID生成**: ULID (Universally Unique Lexicographically Sortable Identifier)

---

## テーブル設計

### 1. `coupons` テーブル（クーポンマスタ）

店舗が作成するクーポンのテンプレート・基本情報を管理

| カラム名 | データ型 | 制約 | 説明 |
|---------|---------|-----|------|
| id | ULID | PRIMARY KEY | ULID |
| shop_id | ULID | NOT NULL, FK | 店舗ID |
| title | VARCHAR(255) | NOT NULL | クーポンタイトル（例: "コーヒー1杯無料"） |
| description | TEXT | NULL | 詳細説明 |
| discount_type | ENUM | NOT NULL | 割引タイプ ('percentage', 'fixed', 'free') |
| discount_value | DECIMAL(10,2) | DEFAULT 0 | 割引額・割引率 |
| conditions | TEXT | NULL | 利用条件 |
| image_url | VARCHAR(500) | NULL | クーポン画像URL |
| is_active | BOOLEAN | DEFAULT true | クーポンマスタの有効/無効 |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 更新日時 |

**外部キー制約**
- `shop_id` → `shops(id)` ON DELETE CASCADE

**インデックス**
- `idx_is_active` (is_active)

**Laravelマイグレーション記法**
```php
$table->ulid('id')->primary();
$table->foreignUlid('shop_id')->constrained('shops')->onDelete('cascade');
```

---

### 2. `coupon_issues` テーブル（クーポン発行）

実際にクーポンを発行する際の情報を管理

| カラム名 | データ型 | 制約 | 説明 |
|---------|---------|-----|------|
| id | ULID | PRIMARY KEY | ULID |
| coupon_id | ULID | NOT NULL, FK | クーポンマスタID |
| shop_id | ULID | NOT NULL, FK | 店舗ID |
| issue_type | ENUM | NOT NULL | 発行タイプ ('spot', 'scheduled') |
| start_time | TIMESTAMP | NOT NULL | 有効開始時刻 |
| end_time | TIMESTAMP | NOT NULL | 有効終了時刻 |
| duration_minutes | INT | NOT NULL | 持続時間（分） |
| max_acquisitions | INT | NULL | 取得人数上限（NULLは無制限） |
| current_acquisitions | INT | DEFAULT 0 | 現在の取得数 |
| schedule_pattern | VARCHAR(100) | NULL | cron形式のスケジュール |
| schedule_enabled | BOOLEAN | DEFAULT false | スケジュール有効/無効 |
| status | ENUM | DEFAULT 'active' | ステータス ('active', 'expired', 'full', 'cancelled') |
| is_active | BOOLEAN | DEFAULT true | 発行の有効/無効 |
| issued_by | BIGINT | NULL, FK | 発行した管理者ID (shop_admins) |
| issued_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 発行日時 |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 更新日時 |

**外部キー制約**
- `coupon_id` → `coupons(id)` ON DELETE CASCADE
- `shop_id` → `shops(id)` ON DELETE CASCADE
- `issued_by` → `shop_admins(id)` ON DELETE SET NULL

**インデックス**
- `idx_status` (status)
- `idx_start_end_time` (start_time, end_time)
- `idx_schedule_enabled` (schedule_enabled)

**Laravelマイグレーション記法**
```php
$table->ulid('id')->primary();
$table->foreignUlid('coupon_id')->constrained('coupons')->onDelete('cascade');
$table->foreignUlid('shop_id')->constrained('shops')->onDelete('cascade');
$table->foreignId('issued_by')->nullable()->constrained('shop_admins')->onDelete('set null');
```

---

### 3. `coupon_acquisitions` テーブル（クーポン取得）

ユーザーがクーポンを取得・使用した記録を管理

| カラム名 | データ型 | 制約 | 説明 |
|---------|---------|-----|------|
| id | ULID | PRIMARY KEY | ULID |
| coupon_issue_id | ULID | NOT NULL, FK | クーポン発行ID |
| user_id | ULID | NOT NULL, FK | ユーザーID |
| acquired_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 取得日時 |
| used_at | TIMESTAMP | NULL | 使用日時 |
| expired_at | TIMESTAMP | NOT NULL | 有効期限 |
| status | ENUM | DEFAULT 'active' | ステータス ('active', 'used', 'expired') |
| processed_by | ULID | NULL, FK | 使用処理した管理者ID (shop_admins) |
| usage_notes | TEXT | NULL | 使用時のメモ |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 更新日時 |

**外部キー制約**
- `coupon_issue_id` → `coupon_issues(id)` ON DELETE CASCADE
- `user_id` → `users(id)` ON DELETE CASCADE
- `processed_by` → `shop_admins(id)` ON DELETE SET NULL

**インデックス**
- `unique_user_issue` UNIQUE (user_id, coupon_issue_id) - 1人1つまで
- `idx_status` (status)
- `idx_expired_at` (expired_at)

**Laravelマイグレーション記法**
```php
$table->ulid('id')->primary();
$table->foreignUlid('coupon_issue_id')->constrained('coupon_issues')->onDelete('cascade');
$table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
$table->foreignUlid('processed_by')->nullable()->constrained('shop_admins')->onDelete('set null');
```

---

### 4. `coupon_schedules` テーブル（スケジュール管理）

定期的なクーポン発行のスケジュール管理

| カラム名 | データ型 | 制約 | 説明 |
|---------|---------|-----|------|
| id | ULID | PRIMARY KEY | ULID |
| coupon_id | ULID | NOT NULL, FK | クーポンマスタID |
| shop_id | ULID | NOT NULL, FK | 店舗ID |
| schedule_name | VARCHAR(255) | NOT NULL | スケジュール名（例: "平日ランチタイム"） |
| cron_expression | VARCHAR(100) | NOT NULL | cron形式の実行スケジュール |
| duration_minutes | INT | NOT NULL | 持続時間（分） |
| max_acquisitions | INT | NULL | 取得人数上限 |
| valid_from | DATE | NOT NULL | スケジュール開始日 |
| valid_until | DATE | NULL | スケジュール終了日（NULLは無期限） |
| is_active | BOOLEAN | DEFAULT true | スケジュールの有効/無効 |
| last_executed_at | TIMESTAMP | NULL | 最後に実行された時刻 |
| next_execution_at | TIMESTAMP | NULL | 次回実行予定時刻 |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | 更新日時 |

**外部キー制約**
- `coupon_id` → `coupons(id)` ON DELETE CASCADE
- `shop_id` → `shops(id)` ON DELETE CASCADE

**インデックス**
- `idx_is_active` (is_active)
- `idx_next_execution` (next_execution_at)

**Laravelマイグレーション記法**
```php
$table->ulid('id')->primary();
$table->foreignUlid('coupon_id')->constrained('coupons')->onDelete('cascade');
$table->foreignUlid('shop_id')->constrained('shops')->onDelete('cascade');
```

---

## テーブル関係図

```
shops (既存)
├── coupons (1:多)
│   ├── coupon_issues (1:多)
│   │   └── coupon_acquisitions (1:多)
│   └── coupon_schedules (1:多)
└── shop_admins (既存)

users (既存)
└── coupon_acquisitions (1:多)
```

---

## ビジネスロジック

### クーポン発行フロー
1. **スポット発行**: 管理者が「今すぐ発行」ボタンを押すと即座に有効になる
2. **スケジュール発行**: 事前に設定したスケジュールに従って自動発行

### 取得制限
- `max_acquisitions` で人数制限を設定
- `current_acquisitions` で現在の取得数をカウント
- 上限に達したクーポンは `status` が 'full' になる

### 有効期限管理
- `start_time` と `end_time` で発行全体の有効期間を管理
- `coupon_acquisitions.expired_at` で個別取得の有効期限を管理
- 期限切れのクーポンは `status` が 'expired' になる

### 使用処理フロー
1. ユーザーがクーポンを取得 → `coupon_acquisitions` レコード作成
2. 店舗でクーポン提示 → 店員が確認
3. 店員が使用処理 → `used_at`, `processed_by`, `status='used'` を更新

### 位置情報連携
- `shops` テーブルの `latitude`, `longitude` を使用
- API側で距離計算を行い、指定範囲内のクーポンのみ返却

---

## 実装予定機能

### Phase 1: 基本機能
- [x] データベース設計
- [x] マイグレーション作成
- [x] Eloquentモデル作成
- [ ] 基本CRUD API実装
- [ ] Seederデータ作成

### Phase 2: 発行・取得機能
- [ ] スポット発行機能
- [ ] クーポン取得API
- [ ] 位置情報ベースの配信

### Phase 3: 高度な機能
- [ ] スケジュール発行（cron job）
- [ ] リアルタイム通知（WebSocket）
- [ ] 分析・統計機能

---

## Eloquentモデル機能

### Couponモデル
- **リレーション**: Shop, CouponIssue, CouponSchedule
- **アクセサ**: formatted_discount (割引表示)
- **スコープ**: active, forShop, byDiscountType

### CouponIssueモデル
- **リレーション**: Coupon, Shop, ShopAdmin, CouponAcquisition
- **アクセサ**: is_available, remaining_count, time_remaining
- **メソッド**: incrementAcquisition(), checkExpiration()
- **スコープ**: active, available, forShop, byIssueType

### CouponAcquisitionモデル
- **リレーション**: CouponIssue, User, ShopAdmin
- **アクセサ**: is_expired, is_usable, time_until_expiry
- **メソッド**: markAsUsed(), generateQrData(), validateQrData()
- **スコープ**: active, used, expired, usable, forUser

### CouponScheduleモデル
- **リレーション**: Coupon, Shop
- **メソッド**: calculateNextExecution(), execute(), validateCronExpression()
- **スコープ**: active, forShop

---

## 注意事項

### パフォーマンス考慮
- 位置情報検索では空間インデックスの使用を検討
- リアルタイム性が求められるため、キャッシュ戦略が重要
- 期限切れクーポンの定期的なステータス更新

### セキュリティ
- クーポン取得時の重複チェック（unique制約）
- QRコード署名による不正利用防止
- 不正な時刻操作への対策
- レート制限の実装

### 運用
- 期限切れクーポンの定期削除
- 統計データの収集・分析
- 障害時の復旧手順
- スケジュール実行の監視

---

*最終更新: 2024年12月*
*実装状況: Phase 1 完了（DB設計、マイグレーション、モデル）* 