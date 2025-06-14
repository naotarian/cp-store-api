# CP Store API アーキテクチャドキュメント

## 概要

CP Store APIは、クーポン配布システムを中心とした店舗管理プラットフォームのバックエンドAPIです。Laravel 12フレームワークをベースに、クリーンアーキテクチャの原則に従って設計されています。

## 技術スタック

- **フレームワーク**: Laravel 12.x
- **PHP**: 8.2+
- **認証**: Laravel Sanctum
- **データベース**: MySQL/PostgreSQL
- **テスト**: PHPUnit
- **コード品質**: Laravel Pint, PHPStan

## アーキテクチャ概要

本APIは以下の層構造で設計されています：

```
┌─────────────────────────────────────┐
│           Presentation Layer        │
│  (Controllers, Middleware, Routes)  │
├─────────────────────────────────────┤
│           Application Layer         │
│         (Use Cases, DTOs)           │
├─────────────────────────────────────┤
│            Domain Layer             │
│      (Models, Services, Rules)      │
├─────────────────────────────────────┤
│         Infrastructure Layer        │
│    (Repositories, External APIs)    │
└─────────────────────────────────────┘
```

## ディレクトリ構造

```
app/
├── Common/                 # 共通インターフェース・基底クラス
├── Console/               # Artisanコマンド
├── Exceptions/            # カスタム例外クラス
├── Http/
│   ├── Controllers/       # コントローラー
│   │   ├── Admin/        # 管理者向けAPI
│   │   ├── Auth/         # 認証関連API
│   │   └── Mobile/       # モバイルアプリ向けAPI
│   ├── Middleware/       # ミドルウェア
│   └── Requests/         # フォームリクエスト
├── Models/               # Eloquentモデル
├── Providers/            # サービスプロバイダー
├── Repositories/         # データアクセス層
│   ├── Mobile/          # モバイル向けリポジトリ
│   └── Admin/           # 管理者向けリポジトリ
├── Services/             # ビジネスロジック層
│   ├── Mobile/          # モバイル向けサービス
│   └── Admin/           # 管理者向けサービス
└── UseCases/             # アプリケーション層
    ├── Mobile/          # モバイル向けユースケース
    └── Admin/           # 管理者向けユースケース
```

## 主要コンポーネント

### 1. データモデル

#### 店舗関連
- **Shop**: 店舗情報（名前、住所、営業時間等）
- **ShopAdmin**: 店舗管理者（権限管理、認証）

#### ユーザー関連
- **User**: 一般ユーザー（モバイルアプリ利用者）
- **Favorite**: お気に入り店舗の関連付け
- **Review**: 店舗レビュー・評価

#### クーポン関連
- **Coupon**: クーポンマスタ（タイトル、説明、条件等）
- **CouponSchedule**: クーポン発行スケジュール
- **CouponIssue**: 実際のクーポン発行インスタンス
- **CouponAcquisition**: ユーザーのクーポン取得記録

### 2. API エンドポイント構成

#### モバイルアプリ向けAPI (`/api/`)
```
GET    /shops                    # 店舗一覧
GET    /shops/{id}               # 店舗詳細
GET    /shops/{id}/reviews       # 店舗レビュー一覧
POST   /shops/{id}/reviews       # レビュー投稿
GET    /shops/{id}/coupons       # 店舗のクーポン一覧
GET    /shops/{id}/active-issues # 現在発行中のクーポン

# 認証が必要なエンドポイント
POST   /coupon-issues/{id}/acquire  # クーポン取得
GET    /user/coupons               # 取得済みクーポン一覧
POST   /user/coupons/{id}/use      # クーポン使用
GET    /favorites                  # お気に入り一覧
POST   /favorites                  # お気に入り追加
DELETE /favorites/{id}             # お気に入り削除
```

#### 管理者向けAPI (`/admin/`)
```
GET    /coupons                  # クーポン一覧
POST   /coupons                  # クーポン作成
PUT    /coupons/{id}             # クーポン更新
GET    /active-issues            # 発行中クーポン一覧
POST   /coupons/{id}/issue-now   # 即座にクーポン発行
POST   /issues/{id}/stop         # クーポン発行停止
GET    /schedules                # スケジュール一覧
POST   /schedules                # スケジュール作成
```

### 3. 認証・認可システム

#### 認証方式
- **モバイルアプリ**: カスタムAPIトークン認証
- **管理者**: Laravel Sanctum

#### 権限レベル
- **root**: 全権限（店舗オーナー）
- **manager**: 管理権限（店長）
- **staff**: 基本権限（スタッフ）

### 4. クーポンシステム

#### クーポンライフサイクル
1. **作成**: 管理者がクーポンマスタを作成
2. **スケジュール設定**: 発行タイミング・条件を設定
3. **発行**: 手動またはバッチ処理で発行
4. **取得**: ユーザーがモバイルアプリで取得
5. **使用**: 店舗でQRコード読み取りにより使用

#### 発行タイプ
- **manual**: 管理者による手動発行
- **batch_generated**: スケジュールによる自動発行

#### ステータス管理
- **active**: 利用可能
- **used**: 使用済み
- **expired**: 期限切れ
- **cancelled**: キャンセル済み

## 設計原則

### 1. 責務の分離

#### Controller
- HTTPリクエスト/レスポンスの処理
- バリデーション
- 認証・認可チェック

#### UseCase
- ビジネスロジックの調整
- 複数サービスの組み合わせ
- トランザクション管理

#### Service
- 単一責務のビジネスロジック
- ドメインルールの実装
- 外部サービス連携

#### Repository
- データアクセスの抽象化
- クエリの最適化
- データ変換

### 2. 依存性注入

全てのクラスは依存性注入コンテナを通じて管理され、テスタビリティと保守性を確保しています。

### 3. エラーハンドリング

カスタム例外クラスを使用し、適切なHTTPステータスコードとエラーメッセージを返却します。

```php
// 例: NotFoundException
throw new NotFoundException('店舗が見つかりません');
```

## データベース設計

### 主要テーブル関係

```
shops (店舗)
├── shop_admins (管理者)
├── coupons (クーポンマスタ)
│   ├── coupon_schedules (発行スケジュール)
│   └── coupon_issues (発行インスタンス)
│       └── coupon_acquisitions (取得記録)
├── reviews (レビュー)
└── favorites (お気に入り)

users (ユーザー)
├── favorites (お気に入り)
├── reviews (レビュー)
└── coupon_acquisitions (クーポン取得)
```

### インデックス戦略

- 外部キー制約
- 複合インデックス（検索パフォーマンス向上）
- 一意制約（データ整合性確保）

## セキュリティ

### 1. 認証
- APIトークンによる認証
- トークンのハッシュ化保存
- セッション管理

### 2. 認可
- ロールベースアクセス制御
- リソース所有者チェック
- 店舗スコープ制限

### 3. データ保護
- SQLインジェクション対策（Eloquent ORM）
- XSS対策（入力サニタイゼーション）
- CSRF保護

### 4. QRコード検証
- HMAC署名による改ざん検知
- タイムスタンプによる有効期限チェック

## パフォーマンス最適化

### 1. データベース
- Eager Loading（N+1問題回避）
- インデックス最適化
- クエリキャッシュ

### 2. API
- ページネーション
- レスポンス圧縮
- 適切なHTTPキャッシュヘッダー

## テスト戦略

### 1. 単体テスト
- Service層のビジネスロジック
- Repository層のデータアクセス
- Model層のリレーション・スコープ

### 2. 統合テスト
- API エンドポイント
- 認証・認可フロー
- データベーストランザクション

### 3. 機能テスト
- クーポン取得・使用フロー
- ユーザー登録・ログインフロー
- 管理者権限チェック

## デプロイメント

### 1. 環境構成
- **開発**: Docker Compose
- **ステージング**: AWS ECS/RDS
- **本番**: AWS ECS/RDS（Multi-AZ）

### 2. CI/CD
- GitHub Actions
- 自動テスト実行
- コード品質チェック
- 自動デプロイ

## 監視・ログ

### 1. アプリケーションログ
- Laravel Log（Monolog）
- エラー追跡
- パフォーマンス監視

### 2. インフラ監視
- AWS CloudWatch
- データベース監視
- API レスポンス時間

## 今後の拡張予定

### 1. 機能拡張
- プッシュ通知システム
- 位置情報ベースクーポン
- ポイントシステム
- 分析ダッシュボード

### 2. 技術的改善
- Redis キャッシュ導入
- Elasticsearch 検索機能
- GraphQL API
- マイクロサービス化

## 開発ガイドライン

### 1. コーディング規約
- PSR-12 準拠
- Laravel Pint による自動フォーマット
- 型宣言の徹底

### 2. Git フロー
- feature ブランチ戦略
- プルリクエストレビュー必須
- 自動テスト通過必須

### 3. ドキュメント
- API仕様書（OpenAPI）
- データベース設計書
- デプロイ手順書

---