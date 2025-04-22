# Playwright の公式イメージを使用
FROM mcr.microsoft.com/playwright:v1.51.1-jammy


# 作業ディレクトリを /app に設定
WORKDIR /app

# 依存関係のキャッシュ活用のため、package.json と package-lock.json を先にコピー
COPY package.json ./


# プロジェクト全体のソースコードをコピー
COPY . .


# 依存関係をインストール
RUN npm install



# 必要な Playwright のブラウザをインストール（初回のみ）
#RUN npx playwright install --with-deps
RUN npx playwright install 




# コンテナ起動時にテストを実行するエントリーポイント
ENTRYPOINT ["npx", "playwright", "test"]
