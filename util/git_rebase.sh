
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
