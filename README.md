phpBotAdmin
=================
phpBotAdminはサーバサイドで動作するbot管理ソフトウェアです。
操作はブラウザ上で行います。
現在まだβ版であるため、使いづらい点が多々あると思いますがご了承ください。

推奨環境
-----------
Linux系のOS
PHP >= 5.3.0
cronを毎分動かせること

使い方
-----------
1.
phpBotAdminをあなたのサーバにインストールしてください。ドキュメントルートより上の階層に設置すると安全だと思います。
2.
phpBotAdmin直下の「data」ディレクトリ及び「data」ディレクトリ内にあるファイルのパーミッションを、PHPから読み書きできるように設定してください。
3.
実際にブラウザでphpBotAdminへアクセスするためのPHPファイルを作成します。
例: ドキュメントルート直下へphpBotAdmin.phpを作成
4.
「3」の手順で作成したファイルから、phpBotAdmin直下のindex.phpを読みこむようにします。
例: require_once '../phpBotAdmin/index.php';
5.
ブラウザから「3」で作成したファイルへアクセスしてください。ブラウザ上で初期設定を行います。

OAuth認証の設定について
-----------
以下の記事を参考にTwitter上でアプリケーション登録を行なってください。
http://blog.okumin.com/archives/twitter-bot-2
アクセスレベルは「Read, Write and Access direct messages」、もしくは「Read and Write」に設定してください。

cronの設定
-----------
phpBotAdmin直下の「cron.php」を毎分で実行するように設定してください。

連絡先
-----------
不具合等は
http://okumin.com/mail
からご報告ください。
Twitterアカウント(@okumin)からも受け付けます。

作者について
-----------
NAME: おくみん
WEB: http://okumin.com/
Twitter: @okumin