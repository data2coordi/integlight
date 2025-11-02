#!/bin/bash



clear
./integlight_backup_init.sh restore

# ===== ユーザー設定 =====
BRANCH=$(git rev-parse --abbrev-ref HEAD)
WAIT_SEC=6     # リトライ間隔（秒）
MAX_RETRY=200   # 最大リトライ回数
COMMIT_MSG="${1:-vistest}"

# ===== 1. コミット =====

git commit -m "$COMMIT_MSG  $(date '+%Y-%m-%d %H:%M:%S')" --allow-empty

# ===== 2. プッシュ =====
git push origin $BRANCH

# ===== 3. アクションの commit を待つ =====
echo "GitHub Actions の commit を待っています..."

LOCAL_HASH=$(git rev-parse $BRANCH)
RETRY=0

while [ $RETRY -lt $MAX_RETRY ]; do
    git fetch origin $BRANCH
    REMOTE_HASH=$(git rev-parse origin/$BRANCH)

    if [ "$LOCAL_HASH" != "$REMOTE_HASH" ]; then
        echo "新しい commit が検出されました"
        # ===== 4. pull =====
        git pull origin $BRANCH
        exit 0
    fi

    echo "まだ更新なし... ($((RETRY+1))/$MAX_RETRY)"
    sleep $WAIT_SEC
    RETRY=$((RETRY+1))
done

echo "タイムアウトしました"
exit 1

