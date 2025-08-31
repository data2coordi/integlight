#!/bin/bash
set -euo pipefail

# バックアップ格納ディレクトリ
BACKUP_DIR=./e2e_backups_pf
UPLOADS_SRC=/home/h95mori/dev_wp_env/html/wp-content/uploads

# MySQL 環境変数をコンテナから取得
MYSQL_ROOT_PW=$(docker compose exec -T db_wpdev printenv MYSQL_ROOT_PASSWORD)
MYSQL_DB=$(docker compose exec -T db_wpdev printenv MYSQL_DATABASE)

mkdir -p "$BACKUP_DIR/uploads"

backup_db() {
  echo "[+] Backing up DB ($MYSQL_DB)..."
  docker compose exec -T db_wpdev \
    mysqldump -u root -p"$MYSQL_ROOT_PW" "$MYSQL_DB" > "$BACKUP_DIR/wp_db.sql"
}

backup_uploads() {
  echo "[+] Backing up uploads..."
  rsync -a "$UPLOADS_SRC/" "$BACKUP_DIR/uploads/"
}

restore_db() {
  echo "[+] Restoring DB ($MYSQL_DB)..."
  docker compose exec -T db_wpdev \
    mysql -u root -p"$MYSQL_ROOT_PW" "$MYSQL_DB" < "$BACKUP_DIR/wp_db.sql"
}



restore_uploads() {
  echo "[+] Restoring uploads..."

  sudo rsync -a --no-perms --no-owner --no-group  --delete "$BACKUP_DIR/uploads/" "$UPLOADS_SRC/"

  sudo chown -R 33:tape "$UPLOADS_SRC/"
  sudo chmod -R g+w "$UPLOADS_SRC/"

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
