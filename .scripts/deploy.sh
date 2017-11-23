#!/bin/bash

source .scripts/version-extractor.sh

# trusty環境でのみリリースするように
if [ "$BUILD_DIST" != "trusty" ]
then
  echo "デプロイはtrusty環境で行われます。"
  exit 0
fi

# ブランチがmasterではない、もしくはPRであった場合に終了
if [ "$TRAVIS_BRANCH" != "master" ] || [ "$TRAVIS_PULL_REQUEST" != "false" ]
then
  echo "ブランチがmasterではない、もしくはPull Requestである為リリースは行われません。"
  exit 0
fi

# バージョン取得
CURRENT_VERSION=$(version_from_readme)
# .git削除のためコピー
cp -r ./ ../push7-publishee
# 作業する為に移動
cd ../
# subversionの中に.gitを保存したくないので削除
rm -rf ./push7-publishee/.git

# svnリポジトリをcheckout
svn checkout https://plugins.svn.wordpress.org/push7/
cd ./push7/

# subversionに現在のタグリリースがある場合に終了
if [ -d "./tags/$CURRENT_VERSION" ]
then
  echo "バージョン $CURRENT_VERSION はすでにリリースされています。"
  exit 0
fi

# 現状のtrunkを削除
svn remove ./trunk
cp -r ../push7-publishee ./trunk
svn add ./trunk
# タグを作成
cp -r ../push7-publishee "./tags/$CURRENT_VERSION"
svn add "./tags/$CURRENT_VERSION"

# コミットする
svn commit -m "release for $TRAVIS_COMMIT" --username $WP_USER --password $WP_PASSWORD --no-auth-cache --non-interactive

# svnコミットのステータスをキャッシュ
code=$?
# 成功時は成功したことを表示
[ $code -eq 0 ] && echo "バージョン $CURRENT_VERSION がリリースされました。" || echo "バージョン $CURRENT_VERSION のリリースが失敗しました。"
# キャッシュしたステータスで終了
exit $code
