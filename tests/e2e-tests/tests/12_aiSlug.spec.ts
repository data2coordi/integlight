import { test, expect } from "@playwright/test";
import { Admin } from "../utils/commonClass";

const POST_DATA = {
  title: "これは日本語のテスト投稿のタイトルです。AIスラッグを検証します。",
};
test.describe("Gemini AI設定管理画面", () => {
  test("AIスラッグ生成をOFFにする", async ({ page }) => {
    // 管理画面にログイン済み前提
    await page.goto(
      "/wp-admin/admin.php?page=aurora-design-blocks&tab=gemini_ai"
    );

    // チェックボックスを取得
    const aiCheckbox = page.locator(
      'input[name="aurora_gemini_ai_options[ai_slug_enabled]"]'
    );

    // ONの場合はOFFにする
    if (await aiCheckbox.isChecked()) {
      await aiCheckbox.uncheck();
    }

    // 保存ボタンをクリック
    await page.click('input[type="submit"][value="変更を保存"]');

    // 成功メッセージが表示されることを確認
    const successNotice = page.locator(".notice-success");
    await expect(successNotice).toHaveText(/設定を保存しました/);
  });
});
test.describe("Gemini AI スラッグ自動生成機能 E2Eテスト (URL取得安定版)", () => {
  test("E2E: 新規投稿 → AIスラッグ生成 → URLで検証", async ({ page }) => {
    console.log("[AI Slug Test] ===== START =====");

    // ---------------------------
    // 2. 新規投稿作成
    // ---------------------------
    await page.goto("/wp-admin/post-new.php");
    await page.getByLabel("タイトルを追加").fill(POST_DATA.title);

    // 公開（2段階）
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

    // 保存完了スナックバー待機
    await page.waitForSelector(".components-snackbar-list", {
      state: "attached",
      timeout: 30000,
    });

    // ---------------------------
    // 3. 「投稿を表示」ボタンからURLを取得
    // ---------------------------
    console.log("→ 投稿URL取得開始");

    const viewPostLink = await page.waitForSelector(
      ".post-publish-panel__postpublish-buttons a.is-primary",
      { timeout: 15000 }
    );

    const permalink = await viewPostLink.getAttribute("href");
    if (!permalink) {
      throw new Error("投稿を表示リンクの href が取得できませんでした。");
    }

    console.log(`→ 取得URL: ${permalink}`);

    // ---------------------------
    // 4. スラッグ抽出と形式検証
    // ---------------------------
    const url = new URL(permalink);
    const slug = decodeURIComponent(url.pathname)
      .split("/")
      .filter(Boolean)
      .pop();

    if (!slug) {
      throw new Error("URLからスラッグを抽出できませんでした。");
    }

    console.log(`→ 抽出スラッグ: ${slug}`);

    const slugRegex = /^[a-z0-9\-]+$/;
    expect(slug).toMatch(slugRegex);

    console.log("[AI Slug Test] ===== END =====");
  });
});
