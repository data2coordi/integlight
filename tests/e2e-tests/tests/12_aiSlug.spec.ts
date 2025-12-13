import { test, expect, Page } from "@playwright/test";

const POST_DATA = {
  title: "これは日本語のテスト投稿のタイトルです。AIスラッグを検証します。",
};

/**
 * 管理画面でAIスラッグ生成のON/OFFとAIキーの設定
 */
async function setAiSlugEnabled(page: Page, enabled: boolean) {
  await page.goto(
    "/wp-admin/admin.php?page=aurora-design-blocks&tab=gemini_ai"
  );

  const aiCheckbox = page.locator(
    'input[name="aurora_gemini_ai_options[ai_slug_enabled]"]'
  );

  const aiKeyInput = page.locator(
    'input[name="aurora_gemini_ai_options[api_key]"]'
  );

  // AIキー管理
  if (!enabled) {
    // OFF時はキーをクリアし、グローバル変数に保存
    const currentKey = await aiKeyInput.inputValue();
    globalThis.GEMINI_AI_KEY = currentKey || "";
    await aiKeyInput.fill("");
  } else {
    // ON時はグローバル変数から復元
    if (globalThis.GEMINI_AI_KEY) {
      await aiKeyInput.fill(globalThis.GEMINI_AI_KEY);
    }
  }

  const isChecked = await aiCheckbox.isChecked();
  if (enabled && !isChecked) {
    await aiCheckbox.check();
  } else if (!enabled && isChecked) {
    await aiCheckbox.uncheck();
  }

  await page.click('input[type="submit"][value="変更を保存"]');

  const successNotice = page.locator(".notice-success");
  await expect(successNotice).toHaveText(/設定を保存しました/);
}

/**
 * 新規投稿作成 → 公開 → URL取得
 */
async function createAndPublishPost(
  page: Page,
  title: string
): Promise<string> {
  await page.goto("/wp-admin/post-new.php");
  await page.getByLabel("タイトルを追加").fill(title);

  // 公開ボタン2段階クリック
  await page.getByRole("button", { name: "公開", exact: true }).first().click();
  const publishPanel = page.getByRole("region", {
    name: "エディターの投稿パネル",
  });
  await publishPanel.getByRole("button", { name: "公開", exact: true }).click();

  // 保存完了待機
  await page.waitForSelector(".components-snackbar-list", {
    state: "attached",
    timeout: 30000,
  });

  // URL取得
  const viewPostLink = await page.waitForSelector(
    ".post-publish-panel__postpublish-buttons a.is-primary",
    { timeout: 15000 }
  );
  const permalink = await viewPostLink.getAttribute("href");
  if (!permalink)
    throw new Error("投稿を表示リンクの href が取得できませんでした。");

  return permalink;
}

/**
 * URLからスラッグ抽出
 */
function extractSlugFromUrl(url: string): string {
  const u = new URL(url);
  const slug = decodeURIComponent(u.pathname.replace(/\/$/, ""))
    .split("/")
    .filter(Boolean)
    .pop();
  if (!slug) throw new Error("URLからスラッグを抽出できませんでした。");
  return slug;
}

// -----------------------------
// テスト群
// -----------------------------
test.describe("Gemini AI スラッグ自動生成機能OFF E2Eテスト", () => {
  test("AIスラッグ生成をOFFにしてAIキーをクリア", async ({ page }) => {
    await setAiSlugEnabled(page, false);
  });

  test("タイトル先頭とスラッグ一致確認", async ({ page }) => {
    console.log("[AI Slug Test] ===== START =====");

    const permalink = await createAndPublishPost(page, POST_DATA.title);
    console.log(`→ 取得URL: ${permalink}`);

    const slug = extractSlugFromUrl(permalink);
    console.log(`→ 抽出スラッグ: ${slug}`);

    const expectedStart = "これは日本語のテスト投稿";
    console.log(`→ 期待スラッグ先頭: ${expectedStart}`);

    expect(slug.startsWith(expectedStart)).toBe(true);

    console.log("[AI Slug Test] ===== END =====");
  });
});

test.describe("Gemini AI スラッグ自動生成機能 E2Eテスト", () => {
  test("AIスラッグ生成をONにしてAIキーをセット", async ({ page }) => {
    await setAiSlugEnabled(page, true);
  });

  test("新規投稿 → AIスラッグ生成 → URLで検証", async ({ page }) => {
    console.log("[AI Slug Test] ===== START =====");

    const permalink = await createAndPublishPost(page, POST_DATA.title);
    console.log(`→ 取得URL: ${permalink}`);

    const slug = extractSlugFromUrl(permalink);
    console.log(`→ 抽出スラッグ: ${slug}`);

    // 英数字・ハイフンのみかを検証
    const slugRegex = /^[a-z0-9\-]+$/;
    expect(slug).toMatch(slugRegex);

    console.log("[AI Slug Test] ===== END =====");
  });
});
