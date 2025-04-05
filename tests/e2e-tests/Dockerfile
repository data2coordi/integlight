# Playwright の公式イメージを使用
FROM mcr.microsoft.com/playwright:focal

# 作業ディレクトリを /app に設定
WORKDIR /app

# 依存関係のキャッシュ活用のため、package.json と package-lock.json を先にコピー
COPY package.json ./

# 依存関係をインストール
RUN npm install

# 必要な Playwright のブラウザをインストール（初回のみ）
RUN npx playwright install

# プロジェクト全体のソースコードをコピー
COPY . .

# コンテナ起動時にテストを実行するエントリーポイント
ENTRYPOINT ["npx", "playwright", "test"]
