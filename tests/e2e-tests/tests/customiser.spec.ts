import { test, expect, type Page } from "@playwright/test";
import {
  openCustomizer,
  saveCustomizer,
  ensureCustomizerRoot,
  activateTheme,
  showCodeOverlay,
} from "../utils/common";

// テストデータを配列でまとめる
const CUSTOMIZER_INPUTS = [
  {
    name: "GA",
    buttonName: "Google Analytics 設定",
    label: "Google Analytics 測定ID",
    code: "UA-12345678-1",
    outputTarget: "body",
    optimized: true, // 高速化オプション対象
  },
  {
    name: "GTM-head",
    buttonName: "Google Tag Manager 設定",
    label: "headタグに出力するコード",
    code: "<script>//GTM-head-12345678-1</script>",
    outputTarget: "head",
  },
  {
    name: "GTM-body",
    buttonName: "Google Tag Manager 設定",
    label: "bodyタグ開始直後に出力するコード",
    code: "<script>//GTM-body-12345678-1</script>",
    outputTarget: "body",
  },
  {
    name: "adSense",
    buttonName: "Googleアドセンス自動広告",
    label: "アドセンス自動広告コード",
    code: "<script>//adSense-head-12345678-1</script>",
    outputTarget: "head",
  },
];

test.describe("カスタマイザー全パターンまとめテスト", () => {
  test("E2E: 4パターン入力・保存・フロント確認・復元確認", async ({ page }) => {
    // ---------------------------
    // 1. カスタマイザーを開き、各パターン入力
    // ---------------------------
    await openCustomizer(page);

    for (const input of CUSTOMIZER_INPUTS) {
      console.log(`@@@@@テスト中@@@@@: ${input.name}`);
      await page.getByRole("button", { name: input.buttonName }).click();

      const field = page.getByLabel(input.label);
      await field.fill("");
      await field.fill(input.code);

      await ensureCustomizerRoot(page);
    }

    // ---------------------------
    // 2. 高速化オプション ON で全パターン保存・フロント確認
    // ---------------------------
    for (const input of CUSTOMIZER_INPUTS) {
      if (input.optimized) {
        const label = page.getByLabel("高速化オプションを有効にする");
        await label.check();
      }

      await saveCustomizer(page);
      const content = await page.locator(input.outputTarget).innerHTML();
      expect(content).toContain(input.code);
    }

    // ---------------------------
    // 3. 高速化オプション OFF で全パターン保存・フロント確認
    // ---------------------------
    for (const input of CUSTOMIZER_INPUTS) {
      if (input.optimized) {
        const label = page.getByLabel("高速化オプションを有効にする");
        await label.uncheck();
      }

      await saveCustomizer(page);
      const content = await page.locator(input.outputTarget).innerHTML();
      expect(content).toContain(input.code);
    }

    // ---------------------------
    // 4. 保存後、カスタマイザー内で値が復元されるか確認
    // ---------------------------
    for (const input of CUSTOMIZER_INPUTS) {
      await page.getByRole("button", { name: input.buttonName }).click();
      const field = page.getByLabel(input.label);
      await expect(field).toHaveValue(input.code);
      await ensureCustomizerRoot(page);
    }

    // ---------------------------
    // 5. フロントで出力されているか確認
    // ---------------------------
    await page.goto("/", { waitUntil: "networkidle" });
    for (const input of CUSTOMIZER_INPUTS) {
      const content = await page.locator(input.outputTarget).innerHTML();
      expect(content).toContain(input.code);
      await showCodeOverlay(page, input.code);
    }

    // ---------------------------
    // 6. Twenty Twentyでフロント出力を確認
    // ---------------------------
    await activateTheme(page, "twentytwenty");
    await page.goto("/", { waitUntil: "networkidle" });
    for (const input of CUSTOMIZER_INPUTS) {
      const content = await page.locator(input.outputTarget).innerHTML();
      expect(content).toContain(input.code);
    }

    // ---------------------------
    // 7. Twenty Twentyで保存した値が復元されるか確認
    // ---------------------------
    await openCustomizer(page);
    for (const input of CUSTOMIZER_INPUTS) {
      await page.getByRole("button", { name: input.buttonName }).click();
      const field = page.getByLabel(input.label);
      await expect(field).toHaveValue(input.code);
      await ensureCustomizerRoot(page);
    }

    // 元テーマに戻す
    await activateTheme(page, "integlight");
  });
});
