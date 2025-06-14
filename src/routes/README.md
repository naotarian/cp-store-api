# CP-Store API ルート設計

## 📁 ルートファイル構成

```
routes/
├── api.php          # エンドユーザー用API (モバイル + Web)
├── admin.php        # 店舗管理者用API  
├── root.php         # 運営管理者用API (将来実装)
└── web.php          # Webフロントエンド用 (未使用)
```

## 🎯 認証システム対応表

| 対象ユーザー | テーブル | ガード | ルートファイル | URLプレフィックス | 認証方式 |
|---|---|---|---|---|---|
| エンドユーザー | `users` | `user` | `api.php` | `/api/*` | ApiTokenMiddleware |
| 店舗管理者 | `shop_admins` | `shop` | `admin.php` | `/admin/*` | Laravel Sanctum |
| 運営管理者 | `roots` | `root` | `root.php` | `/root/*` | Laravel Sanctum (将来) |

## 🔐 認証フロー

### 1. エンドユーザー (モバイル + Web)
```
POST /api/auth/login
→ User モデルでログイン
→ カスタムAPIトークン生成 (api_token フィールド)
→ ApiTokenMiddleware で認証チェック
→ Auth::guard('user')->user() でユーザー取得
```

### 2. 店舗管理者
```
POST /admin/auth/login  
→ ShopAdmin モデルでログイン
→ Sanctumトークン生成 (personal_access_tokens テーブル)
→ auth:shop ミドルウェアで認証チェック
→ Auth::user() でユーザー取得
```

### 3. 運営管理者 (将来実装)
```
POST /root/auth/login
→ Root モデルでログイン (予定)
→ Sanctumトークン生成 (予定)  
→ auth:root ミドルウェアで認証チェック (予定)
→ Auth::user() でユーザー取得 (予定)
```

## 🛡️ セキュリティレベル

1. **エンドユーザー**: 基本認証 (カスタムトークン)
2. **店舗管理者**: 中レベル認証 (Sanctum + 店舗スコープ)  
3. **運営管理者**: 高レベル認証 (Sanctum + 全システム権限)

## 📝 将来の拡張計画

- [ ] Web版エンドユーザー画面 (api.php を流用)
- [ ] 運営管理画面 (root.php を追加)
- [ ] マルチテナント対応の強化
- [ ] OAuth連携 (Google, Apple, LINE等) 