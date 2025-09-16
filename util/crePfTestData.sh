#!/bin/bash


####################s

#wp post delete $(wp post list --post_type=post --post_status=publish --format=csv | grep "TEST_DATA" | cut -d',' -f1 | tr '\n' ' ') --force


# 設定値
SOURCE_TITLE="TEST_ORG_DATA"   # 元データのタイトル
POST_COUNT=2000                  # 作成する投稿数
THUMBNAIL_SOURCE_LIMIT=20      # サムネイル取得件数
NEW_POST_BASE="TEST_DATA"
POST_AUTHOR="tech"              # 投稿者スラッグまたはユーザーID

# 投稿者IDを取得（ユーザー名 or スラッグから）
AUTHOR_ID=$(wp user get $POST_AUTHOR --field=ID)
if [ -z "$AUTHOR_ID" ]; then
  echo "Error: 投稿者 '${POST_AUTHOR}' が存在しません。"
  exit 1
fi

# 元データの投稿IDを取得
SOURCE_ID=$(wp post list --post_type=post --title="$SOURCE_TITLE" --field=ID --posts_per_page=1)
if [ -z "$SOURCE_ID" ]; then
  echo "Error: タイトル '${SOURCE_TITLE}' の投稿が見つかりません。"
  exit 1
fi

# 本文を取得
SOURCE_CONTENT=$(wp post get $SOURCE_ID --field=post_content)

# 最新投稿からサムネイルを取得
THUMBNAILS=($(wp post list --post_type=post --posts_per_page=$THUMBNAIL_SOURCE_LIMIT --orderby=date --order=DESC --field=ID))
THUMBNAIL_IDS=()
for ID in "${THUMBNAILS[@]}"; do
  THUMB_ID=$(wp post meta get $ID _thumbnail_id)
  THUMBNAIL_IDS+=("$THUMB_ID")
done

# 投稿を複数作成
for i in $(seq 1001 $POST_COUNT); do
  TITLE="${NEW_POST_BASE} $i"

  # サムネイルを循環して割り当て
  THUMB_INDEX=$(( (i - 1) % ${#THUMBNAIL_IDS[@]} ))
  THUMBNAIL_ID=${THUMBNAIL_IDS[$THUMB_INDEX]}

  wp post create \
    --post_title="$TITLE" \
    --post_content="$SOURCE_CONTENT" \
    --post_status=publish \
    --post_author=$AUTHOR_ID \
    --meta_input="{\"_thumbnail_id\": $THUMBNAIL_ID}" \
    --porcelain

  echo "投稿 $i 作成完了: 投稿者ID=$AUTHOR_ID, サムネイルID=$THUMBNAIL_ID"
done
exit
####################e




















####################s

wp post delete $(wp post list --post_type=post --post_status=publish --format=csv | grep "TEST_DATA" | cut -d',' -f1 | tr '\n' ' ') --force

# 設定値
POST_LIMIT=14      # まとめ対象件数
POST_COUNT=5       # まとめ投稿を何件作成するか
NEW_POST_BASE="TEST_DATA"
CATEGORY="how-to-use-integlight"  # 対象カテゴリのスラッグ

# 最新の投稿IDを取得（新しい順）
POST_IDS=($(wp post list --post_type=post --posts_per_page=$POST_LIMIT --orderby=date --order=DESC \
      --category_name=$CATEGORY \
      --field=ID))

BASE_CONTENT=""
THUMBNAILS=()

# 各投稿の本文とサムネイルを収集
for ID in "${POST_IDS[@]}"; do
  POST_TITLE=$(wp post get $ID --field=post_title)
  POST_CONTENT=$(wp post get $ID --field=post_content)
  THUMBNAIL_ID=$(wp post meta get $ID _thumbnail_id)   # ← ここを修正

  BASE_CONTENT="$BASE_CONTENT\n\n## $POST_TITLE\n\n$POST_CONTENT\n\n---"
  THUMBNAILS+=("$THUMBNAIL_ID")
done

# 複数のまとめ投稿を生成
for i in $(seq 1 $POST_COUNT); do
  TITLE="${NEW_POST_BASE}$i"

  # サムネイルを順番に割り当て（足りなければループ）
  THUMBNAIL_INDEX=$(( (i - 1) % POST_LIMIT ))
  THUMBNAIL_ID=${THUMBNAILS[$THUMBNAIL_INDEX]}

  wp post create \
    --post_title="$TITLE" \
    --post_content="$BASE_CONTENT" \
    --post_status=publish \
    --meta_input="{\"_thumbnail_id\": $THUMBNAIL_ID}" \
    --porcelain

  echo "テスト投稿 $i 作成完了: サムネイルID=$THUMBNAIL_ID $(date)"
done

###################e





exit



####################s
TEMPLATE_ID=7880
TEMPLATE_CONTENT=$(wp post get $TEMPLATE_ID --field=post_content)

for i in {1..3}; do
  CONTENT="$TEMPLATE_CONTENT $TEMPLATE_CONTENT $TEMPLATE_CONTENT $TEMPLATE_CONTENT $TEMPLATE_CONTENT 番号 $i"
  wp post create --post_title="テスト投稿 $i" \
                 --post_content="$CONTENT" \
                 --post_status=publish \
                --meta_input='{"_thumbnail_id": 7888}' \
                 --porcelain
echo $i `date`
done
####################e




####################s
#wp post list --post_type=post --post_status=publish --format=csv | grep "テスト投稿" | cut -d',' -f1
wp post delete $(wp post list --post_type=post --post_status=publish --format=csv | grep "テスト投稿" | cut -d',' -f1 | tr '\n' ' ') --force
####################e


####################s
wp post meta get 7880 _thumbnail_id
####################e