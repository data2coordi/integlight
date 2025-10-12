import { test, expect, Page } from "@playwright/test";

/**
 * AuroraDesignBlocks_add_ogp_meta_tags() の OGPメタタグ出力検証a
 */
test.describe("ORP :", () => {
  // -------------------------------------------
  // 共通検証関数
  // -------------------------------------------
  const checkOGPMetaTags = async (page: Page) => {
    const selectors = {
      title: page.locator('meta[property="og:title"]'),
      desc: page.locator('meta[property="og:description"]'),
      url: page.locator('meta[property="og:url"]'),
      image: page.locator('meta[property="og:image"]'),
      site: page.locator('meta[property="og:site_name"]'),
      locale: page.locator('meta[property="og:locale"]'),
      type: page.locator('meta[property="og:type"]'),
    };

    await expect(selectors.title).toHaveAttribute("content", /.+/);
    await expect(selectors.desc).toHaveAttribute("content", /.+/);
    await expect(selectors.url).toHaveAttribute("content", /https?:\/\//);
    await expect(selectors.image).toHaveAttribute("content", /https?:\/\//);
    await expect(selectors.site).toHaveAttribute("content", /.+/);
    await expect(selectors.locale).toHaveAttribute("content", /.+/);
    await expect(selectors.type).toHaveAttribute("content", "website");
  };

  // -------------------------------------------
  // テストケース定義（URLと説明文のみ）
  // -------------------------------------------
  const pages = [
    {
      name: "投稿ページでOGPメタタグが正しく出力される",
      path: "/sidefire-tax-simulation/",
    },
    {
      name: "トップページでOGPメタタグが正しく出力される",
      path: "/",
    },
  ];

  // -------------------------------------------
  // テーブル駆動テスト実行
  // -------------------------------------------
  for (const p of pages) {
    test(p.name, async ({ page }) => {
      console.log(`\n--- ${p.name} のテスト開始 ---`);
      await page.goto(p.path);
      await checkOGPMetaTags(page);
      console.log(`\n--- ${p.name} のテスト終了 ---`);
    });
  }
});
