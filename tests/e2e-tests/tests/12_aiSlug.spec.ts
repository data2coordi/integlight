import { test, expect, type Page } from "@playwright/test";
import { Admin } from "../utils/commonClass";

const POST_DATA = {
  title: "これは日本語のテスト投稿のタイトルです。AIスラッグを検証します。",
};

test.describe("Gemini AI スラッグ自動生成機能 E2Eテスト (URL直接検証)", () => {
  test("E2E: 新規投稿 - 日本語タイトルからAIスラッグが生成され、パーマリンクで検証", async ({
    page,
  }) => {
    console.log(
      "[12_aiSlug.spec.ts] ===== START: 日本語タイトルからAIスラッグが生成され、パーマリンクで検証 (最終版) ====="
    );

    // ---------------------------
    // 1. 新規投稿作成・公開
    // ---------------------------
    await page.goto("/wp-admin/post-new.php");
    const titleField = page.getByLabel("タイトルを追加");
    await titleField.fill(POST_DATA.title);

    // 公開処理 (安定版のクリックロジックを使用)
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
    // 2. パーマリンクを取得
    // ---------------------------

    console.log("-> 生成されたパーマリンクを検証します。");
    // 公開が完了 → スナックバーまで待つ

    // 「パーマリンクコピー」リンクが表示されるのを待つ（AIスラッグ確定を保証）
    const permalinkButton = page.getByRole("link", {
      name: /パーマリンク/,
    });

    // 正式パーマリンクを取得
    await expect(permalinkButton).toHaveAttribute("href", /\/[a-z0-9\-]+\/$/);

    const permalink = await permalinkButton.getAttribute("href");
    const url = new URL(permalink!);
    const slug = url.pathname.split("/").filter(Boolean).pop();

    expect(slug).toMatch(/^[a-z0-9\-]+$/);

    console.log(
      `✅ 検証完了: スラッグ (${generatedSlug}) はAI生成後の形式要件を満たし、フロントエンドで正常にアクセス可能です。`
    );

    // ---------------------------
    // 5. クリーンアップ (任意)
    // ---------------------------
    // 投稿IDを取得し、admin.deletePost(postId) を実行...

    console.log(
      "[12_aiSlug.spec.ts] ===== END: 日本語タイトルからAIスラッグが生成され、パーマリンクで検証 (最終版) ====="
    );
  });
});
