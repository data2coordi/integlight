import { test, expect } from "@playwright/test";

/**
 * AuroraDesignBlocks_add_ogp_meta_tags()
 * WordPressフロント側に正しいOGPメタタグが出力されるかを確認する
 */

// -------------------------------------------
// PC-01 投稿ページのOGPメタタグ出力確認
// -------------------------------------------
test("ORP : 投稿ページでOGPメタタグが正しく出力される", async ({ page }) => {
  await page.goto("/sidefire-tax-simulation/"); // テスト投稿のURLに置き換え

  // OGPメタタグのリストを取得
  const ogTitle = page.locator('meta[property="og:title"]');
  const ogDesc = page.locator('meta[property="og:description"]');
  const ogUrl = page.locator('meta[property="og:url"]');
  const ogImage = page.locator('meta[property="og:image"]');
  const ogSite = page.locator('meta[property="og:site_name"]');
  const ogLocale = page.locator('meta[property="og:locale"]');
  const ogType = page.locator('meta[property="og:type"]');

  // 検証
  await expect(ogTitle).toHaveAttribute("content", /.+/);
  await expect(ogDesc).toHaveAttribute("content", /.+/);
  await expect(ogUrl).toHaveAttribute("content", /https?:\/\//);
  await expect(ogImage).toHaveAttribute("content", /https?:\/\//);
  await expect(ogSite).toHaveAttribute("content", /.+/);
  await expect(ogLocale).toHaveAttribute("content", /.+/);
  await expect(ogType).toHaveAttribute("content", "website");
});

// -------------------------------------------
// PC-02 トップページのOGPメタタグ出力確認
// -------------------------------------------
test.only("PC-02: トップページでOGPメタタグが正しく出力される", async ({
  page,
}) => {
  await page.goto("/");

  const ogTitle = page.locator('meta[property="og:title"]');
  const ogDesc = page.locator('meta[property="og:description"]');
  const ogUrl = page.locator('meta[property="og:url"]');
  const ogType = page.locator('meta[property="og:type"]');

  await expect(ogTitle).toHaveAttribute("content", /.+/);
  await expect(ogDesc).toHaveAttribute("content", /.+/);
  await expect(ogUrl).toHaveAttribute("content", /https?:\/\//);
  await expect(ogType).toHaveAttribute("content", "website");
});

// -------------------------------------------
// PC-04 サムネイルが存在する場合のOGP画像出力確認
// -------------------------------------------
test("PC-04: 投稿にサムネイルが設定されている場合はOGP画像URLが出力される", async ({
  page,
}) => {
  await page.goto("/sample-post-with-thumbnail");

  const ogImage = page.locator('meta[property="og:image"]');
  const imageUrl = await ogImage.getAttribute("content");

  expect(imageUrl).toMatch(/^https?:\/\/.+\.(jpg|jpeg|png|webp)$/);
});
