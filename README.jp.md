# PHPアプリケーション向けライセンスパッケージ

楕円曲線暗号（EC署名）を使って、署名付きライセンスの発行と検証を行う軽量ヘルパーです。

## 必要要件
- PHP 8.1 以上（OpenSSL 拡張が有効であること）

## インストール

Packagist に公開されている場合:

```bash
composer require maymeow/license
```

このリポジトリをローカルで利用する場合:

```bash
composer install
```

## クイックスタート

ライセンス発行者とアプリケーションのキーを生成し、ライセンスを作成・署名して検証します。

```php
use MayMeow\License\ECCryptographyHelper;
use MayMeow\License\License;
use MayMeow\License\LicenseGenerator;
use MayMeow\License\UnsignedArrayHelper;

require __DIR__ . '/vendor/autoload.php';

$crypto = new ECCryptographyHelper();

// 1) EC鍵ペアを生成
[$issuerPrivateKey, $issuerPublicKey] = $crypto->generateEcKeyPair();

// 2) ライセンスペイロードを作成
$licensePayload = LicenseGenerator::generateLicense(
    name: 'Alice Smith',
    email: 'alice@example.com',
    applicationFeatures: ['blogs' => 5],
    validDays: 365
);

// 3) 発行者の秘密鍵で署名
$signedLicense = LicenseGenerator::signLicense($licensePayload, $issuerPrivateKey);

// 4) アプリで公開鍵を使って検証
$appPublicKeyBytes = UnsignedArrayHelper::convertKeyToUnsignedArray($issuerPublicKey);
$license = new License($appPublicKeyBytes, $signedLicense);

if ($license->isValid()) {
    // 機能制御
    if ($license->hasFeature('blogs')) {
        // 機能を許可
    }
}
```

## API ハイライト
- `LicenseGenerator::generateLicense()` ライセンスのメタデータ（ID、名前、メール、機能、期限）を生成。
- `LicenseGenerator::signLicense()` ライセンスJSONをEC秘密鍵で署名し、base64パッケージを返却。
- `LicenseGenerator::verifyLicense()` EC公開鍵で署名パッケージを検証。
- `License` は署名パッケージをラップし、以下を提供:
  - `isValid()` 署名 + 期限チェック
  - `isExpired()` 期限のみチェック
  - `hasFeature($key)` 機能フラグの存在チェック
  - `getOriginalLiceseData()` 元のbase64ペイロード取得
  - `getFeatureValue($key)` 機能の値を返す（真偽または数値など）

### 署名なしバイト配列として鍵を扱う場合
環境変数や設定で鍵を符号なしバイト配列として保存する場合、`UnsignedArrayHelper` で相互変換できます。

```php
// PEM を配列へ変換
$publicKeyBytes = UnsignedArrayHelper::convertKeyToUnsignedArray($issuerPublicKey);

// 配列を PEM へ戻す
$publicKeyPem = UnsignedArrayHelper::convertUnsignedArrayToKey($publicKeyBytes, 'public');
```

## 追加ヘルパー
- `ECCryptographyHelper` は共有秘密生成（`deriveSharedSecret`）、HKDF、AES-256-GCM の暗号化/復号をサポート。
- `RSACryptographyHelper` は RSA 鍵生成と秘密鍵/公開鍵での暗号化・復号の簡易ヘルパーを提供。

## サンプルの実行
- `php test.php` ライセンスを生成・署名し、エンドツーエンドで検証します。
- `php verificationtest.php` 事前生成されたライセンスパッケージを検証します。

## ライセンス
MIT
