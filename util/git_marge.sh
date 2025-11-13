clear

#exit


git checkout jmaster
git pull origin jmaster

git merge --squash jdev

git commit -m "feat: [タスク名] 〇〇機能の完全な実装"

git push origin jmaster

#exit


# ----- ブランチ統合後のクリーンアップ作業 -----

# 1. リモートの jdev ブランチを強制削除
# リモートが未マージと判断しても強制的に削除します
git push origin --delete jdev

# 2. ローカルの jmaster に作業対象を切り替える
# 既に jmaster にいる場合は省略可能ですが、安全のため実行します
git checkout jmaster

# 3. ローカルの jdev ブランチを強制削除
# -d は未マージを検知して停止しますが、-D は強制的に削除します
git branch -D jdev

# 4. 次の作業のための jdev ブランチを再作成
# 最新の jmaster を基に新しい jdev を作成します
git checkout -b jdev