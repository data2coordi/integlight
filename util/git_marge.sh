clear

#exit


git checkout jmaster
git pull origin jmaster

git merge --squash jdev

git commit -m "feat: [タスク名] 〇〇機能の完全な実装"

git push origin jmaster

#exit


# ローカルのdevブランチに戻り、masterをチェックアウトし直す（ブランチ削除のため）
# リモートのdevブランチを削除
git push origin :jdev 

# ローカルのdevブランチを削除
git branch -d jdev
git checkout -b jdev