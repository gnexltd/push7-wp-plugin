#!/bin/bash

# ブランチがmasterではない、もしくはPRであった場合に終了
if [ "$TRAVIS_BRANCH" != "master" ] || [ "$TRAVIS_PULL_REQUEST" != "false" ]
then
  echo "ブランチがmasterではない、もしくはPull Requestである為リリースは行われません。"
  exit 0
fi

# バージョン取得
CURRENT_VERSION=$(grep 'Stable tag:' readme.txt | cut -d' ' -f3)
# .git削除のためコピー
cp -r ./ ../push7-publishee
# svn checkoutし作業する為に移動
cd ../
# subversionの中に.gitを保存したくないので削除
rm -rf ./push7-publishee/.git
svn checkout https://plugins.svn.wordpress.org/push7/
cd ./push7/
# subversionに現在のタグリリースがない場合にリリースを行う
if [ ! -d "tags/$CURRENT_VERSION" ]; then
  # 現状のtrunk削除
  svn remove ./trunk
  cp -r ../push7-publishee ./trunk
  svn add ./trunk
  # タグを作成
  cp -r ../push7-publishee "./tags/$CURRENT_VERSION"
  svn add "./tags/$CURRENT_VERSION"
  # コミットする
  svn commit -m "release for $TRAVIS_COMMIT" --username $WP_USER --password $WP_PASSWORD
  [ $? -eq 0 ] && echo "バージョン $CURRENT_VERSION がリリースされました。"
else
  echo "バージョン $CURRENT_VERSION はすでにリリースされています。"
fi
