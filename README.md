# [COACHTECH お問い合わせフォーム]

## 概要
本システムは、一般ユーザーが利用する公開のお問い合わせフォームです。
誰でもお問い合わせを送信でき、管理者はログイン後にその内容を確認・管理します。
【プロジェクトの目的】
トラブル対応にとどまらず、バックオフィス運用の最適化とマーケティングへの貢献を目指します。
・顧客体験（CX）の向上: スピーディで的確なサポートによる顧客満足度の向上
・業務効率化とコスト削減: 一元管理による対応漏れ・二重対応の防止、過去の問い合わせの検索性向上
・データの蓄積と活用: 問い合わせ内容をデータ化し、今後の商品開発やサービス改善にフィードバックする
【実装した機能の概要】
・カテゴリー機能: お問い合わせは必ず1つのカテゴリーに属する、大分類が可能
・タグ機能: お問い合わせに複数のタグを選択できる、細やかな分類が可能
・検索機能: お問い合わせをキーワード等で検索できる、

## ER図


## 環境構築手順
1. Laravelプロジェクトの作成 (Laravel 10.x)

以下のDockerコマンドを実行してください。

docker run --rm \
    -u ""$(id -u):$(id -g)"" \
    -v ""$(pwd):/var/www/html"" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer create-project laravel/laravel:^10.0 contact-form-app

2-1. Laravel Sailのインストール

contact-form-appディレクトリに移動し、以下のコマンドを実行してください。

docker run --rm \
    -u ""$(id -u):$(id -g)"" \
    -v ""$(pwd):/var/www/html"" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer require laravel/sail --dev

2-2. Sailの設定ファイルをパブリッシュ（MySQLを選択）

docker run --rm \
    -u ""$(id -u):$(id -g)"" \
    -v ""$(pwd):/var/www/html"" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    php artisan sail:install --with=mysql

※M1/M2/M3 Mac（Apple Silicon搭載のMac）をお使いの場合、`sail up -d`実行時に以下のエラーが発生することがあります:no matching manifest for linux/arm64/v8
`compose.yaml`を開き、mysqlサービスに`platform: 'linux/amd64'`を追加してください。

mysql:
    image: 'mysql/mysql-server:8.0'
    platform: 'linux/amd64'  # ← この行を追加
    ports:"

3. .env ファイルの設定

.env ファイルを開き、データベース接続情報が以下と一致していることを確認してください。

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

> 重要: DB_HOST は localhost や 127.0.0.1 ではなく、Dockerコンテナ名である mysql を指定します。

4. フロントエンドのセットアップ (Vite & Tailwind CSS)

4-1. NPM依存パッケージのインストール
> 重要: sail npm install を実行する前に、必ずSailコンテナが起動していることを確認してください。
sail npm install

4-2. Tailwind CSSのインストール
sail npm install -D tailwindcss@^3.4.0 postcss autoprefixer
sail npm install alpinejs

4-3. 設定ファイルの生成
sail npx tailwindcss init -p

4-4. Tailwind CSSのテンプレートパス設定
tailwind.config.js を開き、以下のように設定してください。

/** @type {import(""tailwindcss"").Config} */
export default {
  content: [
    ""./resources/**/*.blade.php"",
    ""./resources/**/*.js"",
    ""./resources/**/*.vue"",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

4-5. 提供リポジトリのresourcesディレクトリと入れ替え
以下のリポジトリをクローンし、resourcesディレクトリを丸ごと入れ替えます。
git clone https://github.com/coachtech-prepared-file/Preparedblade-ConfirmationTest-ContactForm.git

4-6. Vite開発サーバーの起動
sail npm run dev
> 注意: sail npm run dev は実行したままにしておく必要があります。

5. phpMyAdminの追加

compose.yaml を開き、mysql サービスの後に以下の設定を追加してください。

    phpmyadmin:
        image: 'phpmyadmin:latest'
        ports:
            - '${FORWARD_PHPMYADMIN_PORT:-8080}:80'
        environment:
            PMA_HOST: mysql
            PMA_USER: '${DB_USERNAME}'
            PMA_PASSWORD: '${DB_PASSWORD}'
        networks:
            - sail
        depends_on:
            - mysql

6. Sailの起動とエイリアス設定

6-1. Sailをバックグラウンドで起動
./vendor/bin/sail up -d

6-2. エイリアスを設定して 'sail' だけでコマンドを実行できるようにする
echo ""alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'"" >> ~/.zshrc

※bash の場合
echo ""alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'"" >> ~/.bashrc

6-3. シェルを再起動するか、新しいターミナルを開いてエイリアスを有効にする
exec $SHELL

7. アプリケーションキーの生成
ルートで以下のコマンドを実行する
sail artisan key:generate

8. データベースのマイグレーションと初期データ投入
以下のコマンドでテーブルを作成し、初期データを投入します。
sail artisan migrate --seed

※既存のデータベースをリセットしたい場合は以下を実行してください。
sail artisan migrate:fresh --seed

## 使用技術
OS : Linax
PHP : 8.2
Laravel : 10.x
DB : MySQL 8.0
Webサーバー : Nginx
フロントエンド : Vite, Tailwind CSS ^3.4.0
開発ツール : Docker, Laravel Sail, phpMyAdmin

## APIエンドポイント一覧
//変更必要　実装したAPIのエンドポイント一覧（メソッド・パス・概要）


## 開発環境URL
http://localhost

## 作成者
圓　まり
