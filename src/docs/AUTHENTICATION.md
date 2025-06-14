r# CP-Store 認証・権限管理設計

## 🏗️ 全体アーキテクチャ

```
┌─────────────────┬─────────────────┬─────────────────┐
│  エンドユーザー   │   店舗管理者    │   運営管理者    │
│    (users)      │ (shop_admins)   │    (roots)      │
├─────────────────┼─────────────────┼─────────────────┤
│ モバイルアプリ    │   管理画面      │   運営画面      │
│ Web版(将来)     │                │   (将来実装)    │
├─────────────────┼─────────────────┼─────────────────┤
│ /api/*          │ /admin/*        │ /root/*         │
│ ApiToken認証    │ Sanctum認証     │ Sanctum認証     │
└─────────────────┴─────────────────┴─────────────────┘
```

## 🔐 認証システム詳細

### 1. エンドユーザー認証 (users)

**対象**: モバイルアプリユーザー + Web版ユーザー(将来)

**認証方式**: カスタムAPIトークン
- テーブル: `users.api_token` フィールド
- ミドルウェア: `ApiTokenMiddleware`
- ガード: `user`

**権限範囲**:
- 店舗情報の閲覧
- レビューの投稿・編集
- お気に入り店舗の管理
- プロフィール管理
- クーポンの取得・使用

### 2. 店舗管理者認証 (shop_admins)

**対象**: 各店舗の管理者・スタッフ

**認証方式**: Laravel Sanctum
- テーブル: `personal_access_tokens` (ULID対応)
- ミドルウェア: `auth:shop`
- ガード: `shop`

**権限範囲**:
- 自店舗データのみアクセス可能 (店舗スコープ)
- クーポン管理 (作成・発行・停止)
- レビュー管理・返信
- 店舗情報の更新
- スタッフ管理 (manager以上)
- 統計・分析の閲覧

**ロール設計**:
```php
// shop_admins.role カラム
'staff'   => 基本操作のみ
'manager' => スタッフ管理 + 全店舗機能
'admin'   => 全権限 + 設定変更
'root'    => 開発者専用 (削除権限等)
```

### 3. 運営管理者認証 (roots - 将来実装)

**対象**: システム運営者 (あなた専用)

**認証方式**: Laravel Sanctum (強化版)
- テーブル: `personal_access_tokens`
- ミドルウェア: `auth:root`  
- ガード: `root`

**権限範囲**:
- 全店舗データへのアクセス
- ユーザー管理
- 店舗管理者の管理
- システム設定
- 統計・分析 (全体)
- システムメンテナンス

## 🛡️ セキュリティ強化策

### データアクセス制御

```php
// 店舗管理者は自店舗のみアクセス可能
class CouponService {
    public function getAllCoupons(ShopAdmin $admin) {
        return Coupon::where('shop_id', $admin->shop_id)->get();
    }
}

// 運営管理者は全店舗アクセス可能 (将来)
class RootCouponService {
    public function getAllCoupons(?int $shopId = null) {
        $query = Coupon::query();
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }
        return $query->get();
    }
}
```

### トークンのスコープ管理

```php
// 店舗管理者 - 店舗スコープ付きトークン
$token = $shopAdmin->createToken('shop-admin', ['shop:' . $shopAdmin->shop_id]);

// 運営管理者 - 全権限トークン (将来)
$token = $root->createToken('root-admin', ['*']);
```

## 📊 権限マトリックス

| 機能 | エンドユーザー | 店舗スタッフ | 店舗マネージャー | 店舗管理者 | 運営管理者 |
|---|---|---|---|---|---|
| 店舗閲覧 | ✅ | ✅ | ✅ | ✅ | ✅ |
| レビュー投稿 | ✅ | ❌ | ❌ | ❌ | ✅ |
| クーポン使用 | ✅ | ❌ | ❌ | ❌ | ✅ |
| クーポン発行 | ❌ | ✅ | ✅ | ✅ | ✅ |
| スタッフ管理 | ❌ | ❌ | ✅ | ✅ | ✅ |
| 店舗設定 | ❌ | ❌ | ❌ | ✅ | ✅ |
| 他店舗管理 | ❌ | ❌ | ❌ | ❌ | ✅ |
| システム管理 | ❌ | ❌ | ❌ | ❌ | ✅ |

## 🔄 今後の実装手順

### Phase 1: 現状の安定化 ✅
- [x] エンドユーザー認証 (ApiToken)
- [x] 店舗管理者認証 (Sanctum + ULID対応)
- [x] クーポン管理機能

### Phase 2: エンドユーザーWeb版
- [ ] Web版フロントエンド開発
- [ ] 既存API (/api/*) の流用
- [ ] レスポンシブ対応

### Phase 3: 運営管理画面
- [ ] roots テーブル作成
- [ ] root_sanctum ガード追加
- [ ] /root/* ルート追加
- [ ] 運営管理画面開発

### Phase 4: 高度な機能
- [ ] OAuth連携 (Google, Apple, LINE)
- [ ] 2FA認証
- [ ] 監査ログ
- [ ] レート制限強化 