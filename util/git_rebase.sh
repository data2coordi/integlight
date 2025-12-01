
#################################################
## スカッシュ版  #####################################
#################################################
clear

#exit

git checkout dev

ver=$(grep -i "^Version:" ../style.css | awk '{print $2}'); \
sed -i "s/define('_INTEGLIGHT_S_VERSION', '[0-9.]*');/define('_INTEGLIGHT_S_VERSION', '$ver');/" ../functions.php

git add ../functions.php
git commit -m "v$ver-release prep at dev"

git checkout master
git pull origin master

git merge --squash dev

git commit -m "douki v$ver-release"

git push origin master

#exit


# ----- ブランチ統合後のクリーンアップ作業 -----

# 1. リモートの dev ブランチを強制削除
# リモートが未マージと判断しても強制的に削除します
git push origin --delete dev

# 2. ローカルの jmaster に作業対象を切り替える
# 既に master にいる場合は省略可能ですが、安全のため実行します
git checkout master

# 3. ローカルの dev ブランチを強制削除
# -d は未マージを検知して停止しますが、-D は強制的に削除します
git branch -D dev

# 4. 次の作業のための dev ブランチを再作成
# 最新の jmaster を基に新しい dev を作成します
git checkout -b dev

exit







#################################################
## rebase版  #####################################
#################################################














git rebase -i HEAD~5
#git push --force-with-lease


exit


# リベース実施
git rebase -i HEAD~20
git push --force-with-lease


# 他の環境用：リベース後のローカルへの取り込み
git fetch origin
git reset --hard origin/ブランチ名


#ベースの途中で,もう再度、リベースの内容を修正する場合
git rebase --edit-todo
#ベースの途中でコンフリクトが起きた場合
git rebase --continue



##########################
# 20コミットをまとめてステージング状態に戻す
git reset --soft HEAD~10
# まとめて新しい1コミットを作成
git commit -m "コミットを集約"
git push --force-with-lease




##########################
#履歴なしで別ブランチから取得
#別リポジトリから取得 ディレクトリ単位
cd ./tests/e2e-tests
git restore --source origin/ビジュアルinit2 ./
