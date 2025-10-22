#!/bin/bash
set -euo pipefail

# バックアップ格納ディレクトリ
BACKUP_DIR=./e2e_backups
# WordPressのuploadsディレクトリのパス
UPLOADS_SRC=/home/xsaurora/auroralab-design.com/public_html/wpdev.auroralab-design.com/wp-content/uploads

# MySQL 接続情報
set -a
source ./utils/.env
set +a


# uploadsディレクトリの所有者とグループ
# Xserverのウェブサーバーユーザーは通常`www-data`ですが、
# 環境によっては異なる場合があります。
UPLOADS_OWNER="xsaurora" 
UPLOADS_GROUP="members" 

# バックアップディレクトリを作成
mkdir -p "$BACKUP_DIR/uploads"

#---
## バックアップ
#---

backup_db() {
  echo "[+] Backing up DB ($MYSQL_DB)..."
  mysqldump -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" -p"$MYSQL_ROOT_PW" "$MYSQL_DB" > "$BACKUP_DIR/wp_db.sql"
}

backup_uploads() {
  echo "[+] Backing up uploads..."
  rsync -a "$UPLOADS_SRC/" "$BACKUP_DIR/uploads/"
}

#---
## リストア
#---

restore_db() {
  echo "[+] Restoring DB ($MYSQL_DB)..."
  mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u "$MYSQL_USER" -p"$MYSQL_ROOT_PW" "$MYSQL_DB" < "$BACKUP_DIR/wp_db.sql"
}

restore_uploads() {
  echo "[+] Restoring uploads..."
  rsync -a --delete "$BACKUP_DIR/uploads/" "$UPLOADS_SRC/"

  echo "[+] Fixing permissions..."
  chown -R "$UPLOADS_OWNER":"$UPLOADS_GROUP" "$UPLOADS_SRC/"
  chmod -R g+w "$UPLOADS_SRC/"
}

case "${1:-}" in
  backup)
    backup_db
    backup_uploads
    ;;
  restore)
    restore_db
    restore_uploads
    ;;
  *)
    echo "Usage: $0 {backup|restore}"
    exit 1
    ;;
esac

echo "[✓] Done."