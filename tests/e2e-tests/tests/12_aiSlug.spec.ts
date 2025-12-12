import { test, expect, type Page } from "@playwright/test";
import { Admin } from "../utils/commonClass"; // ログイン処理を担うAdminクラスをインポート

const POST_DATA = {
  title: "これは日本語のテスト投稿のタイトルです。AIスラッグを検証します。",
};

test.describe("Gemini AI スラッグ自動生成機能 E2Eテスト (URL直接検証 - クリーン版)", () => {
  test("E2E: 新規投稿 - 日本語タイトルからAIスラッグが生成され、パーマリンクで検証", async ({
    page,
  }) => {
    console.log(
      "[12_aiSlug.spec.ts] ===== START: 日本語タイトルからAIスラッグが生成され、パーマリンクで検証 (クリーン版) ====="
    );

    // ---------------------------
    // 2. 新規投稿作成・公開 (AI処理トリガー)
    // ---------------------------
    await page.goto("/wp-admin/post-new.php");
    await page.getByLabel("タイトルを追加").fill(POST_DATA.title);

    // 安定した公開処理
    await page
      .getByRole("button", { name: "公開", exact: true })
      .first()
      .click();
    const publishPanel = page.getByRole("region", {
      name: "エディターの投稿パネル",
    });
    await publishPanel
      .getByRole("button", { name: "公開", exact: true })
      .click();

    // 保存完了を待機 (AI処理完了までの時間も含む)
    await page.waitForSelector(".components-snackbar-list", {
      state: "attached",
      timeout: 30000,
    });

    // ---------------------------
    // 3. パーマリンクを取得し、スラッグを検証
    // ---------------------------
    // ★ UI リロード（DBの最新post_nameを画面に反映）
    await page.reload();
    console.log("-> 生成されたパーマリンクを検証します。");

    // 投稿後の画面上部に出現するパーマリンク表示要素（例: '投稿を表示' リンク）を狙う
    const permalinkLink = page.getByRole("link", {
      name: /投稿を表示/,
      exact: false,
    });

    // AI処理が完了し、スラッグが適切な形式に更新されるまで待機
    await expect(permalinkLink).toHaveAttribute("href", /\/[a-z0-9\-]+\/$/, {
      timeout: 15000,
    });

    const permalink = await permalinkLink.getAttribute("href");
    if (!permalink) {
      throw new Error("パーマリンクを取得できませんでした。");
    }

    // URL解析を行い、スラッグ部分を抽出
    const url = new URL(permalink);
    const slug = decodeURIComponent(url.pathname)
      .split("/")
      .filter(Boolean)
      .pop();

    if (!slug) {
      throw new Error("URLパスからスラッグを抽出できませんでした。");
    }

    // 形式要件の検証: スラッグが半角小文字、数字、ハイフンのみで構成されていること
    const slugRegex = /^[a-z0-9\-]+$/;
    expect(slug).toMatch(slugRegex);
    console.log(`取得されたスラッグ: ${slug}`);

    // ---------------------------
    // 4. フロントエンドでのアクセス可能性を検証 (E2Eの最終確認)
    // ---------------------------

    await page.goto(permalink);
    await expect(
      page.getByRole("heading", { name: POST_DATA.title, exact: true })
    ).toBeVisible();

    console.log(
      `✅ 検証完了: スラッグ (${slug}) はAI生成後の形式要件を満たし、フロントエンドで正常にアクセス可能です。`
    );

    // ---------------------------
    // 5. クリーンアップ (必要に応じて実装)
    // ---------------------------
    // クリーンアップ処理が必要な場合は、ここで実装します。
    // 例: const postId = getPostIdFromUrl(page.url());
    // if (postId) { await admin.deletePost(postId); }

    console.log(
      "[12_aiSlug.spec.ts] ===== END: 日本語タイトルからAIスラッグが生成され、パーマリンクで検証 (クリーン版) ====="
    );
  });
});
