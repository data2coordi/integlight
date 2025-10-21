import { test, expect } from "@playwright/test";
import {
  timeStart,
  logStepTime,
  openCustomizer,
  saveCustomizer,
  ensureCustomizerRoot,
  openHeaderSetting,
} from "../utils/common";

// 共通設定
const BASE_URL = "https://wpdev.auroralab-design.com";

// テスト用設定一覧
const TEST_CONFIGS = {
  inisialCustomiserSetting: {
    effectLabel: "フェード",
    siteType: "エレガント",
  },
  spCustomizerSetting: {
    viewport: { width: 375, height: 800 },
    userAgent:
      "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
    interval: "1",
    mainText: "テストタイトル",
    subText: "これはPlaywrightテストによって入力された説明文です。",
    textPositionTop: "10",
    textPositionLeft: "15",
    textColor: "#ff0000",
    textFont: "yu_mincho",
    imagePartialName: "Firefly-260521",
  },
  pcCustomizerSetting: {
    interval: "1",
    mainText: "テストタイトル",
    subText: "これはPlaywrightテストによって入力された説明文です。",
    textPositionTop: "100",
    textPositionLeft: "150",
    textColor: "#ff0000",
    textFont: "yu_mincho",
    imagePartialName: "Firefly-203280",
  },
};

// ヘッダー設定をヘッダー画像に変更
async function openHeaderImage(page) {
  await page.getByRole("button", { name: "ヘッダー設定" }).click();
  await page.getByRole("button", { name: "2.静止画像設定" }).click();
}

// ヘッダー画像テキスト設定
async function setHeaderImageText(page, config) {
  await openHeaderImage(page);

  // テキスト設定
  await page
    .getByLabel("ヘッダー画像メインテキスト")
    .nth(0)
    .fill(config.mainText);
  await page.getByLabel("ヘッダー画像サブテキスト").nth(0).fill(config.subText);

  await expect(
    page.getByLabel("ヘッダー画像メインテキスト").nth(0)
  ).toHaveValue(config.mainText);
  await expect(page.getByLabel("ヘッダー画像サブテキスト").nth(0)).toHaveValue(
    config.subText
  );

  // テキストカラー設定
  await page.getByRole("button", { name: "色を選択" }).click();
  const input = page.getByLabel("ヘッダー画像テキストの色");
  await input.fill(config.textColor);

  // フォント設定
  const label = page.locator("label", {
    hasText: "ヘッダー画像テキストのフォント",
  });
  const selectId = await label.getAttribute("for");
  if (!selectId)
    throw new Error(
      `ラベル "ヘッダー画像テキストのフォント" に対応する select が見つかりません`
    );
  const select = page.locator(`#${selectId}`);
  await select.waitFor({ state: "visible" });
  await select.selectOption(config.textFont);

  // テキスト位置設定
  await page
    .getByLabel("ヘッダー画像テキストの上位置（px）")
    .fill(config.textPositionTop);
  await page
    .getByLabel("ヘッダー画像テキストの左位置（px）")
    .fill(config.textPositionLeft);
}

// ヘッダー画像設定
async function setHeaderImage(page, config) {
  await openHeaderImage(page);

  await page.getByRole("button", { name: "画像を追加" }).nth(0).click();
  const mediaModal = page.locator(".attachments-browser");
  await mediaModal.waitFor({ state: "visible", timeout: 15000 });

  const searchInput = page.locator("#media-search-input");
  await searchInput.fill(config.imagePartialName);
  await searchInput.press("Enter");

  const targetImage = page
    .locator(`.attachments-browser img[src*="${config.imagePartialName}"]`)
    .first();
  await targetImage.waitFor({ state: "visible", timeout: 15000 });
  await targetImage.click({ force: true });

  await page.getByRole("button", { name: "選択して切り抜く" }).nth(0).click();
  await page.getByRole("button", { name: "画像切り抜き" }).nth(0).click();
}

// 共通テストフロー
async function setHeaderImageDetailSettings(page, config, inisialSetting) {
  await test.step("カスタマイザー画面を開く", () => openCustomizer(page));
  await test.step("ヘッダー有無を設定", () =>
    openHeaderSetting(page, "静止画像"));

  await ensureCustomizerRoot(page);
  await test.step("ヘッダー画像テキストを設定する", () =>
    setHeaderImageText(page, config));

  await ensureCustomizerRoot(page);
  await test.step("ヘッダー画像を設定する", () => setHeaderImage(page, config));

  await test.step("8. 公開ボタンをクリックして変更を保存", () =>
    saveCustomizer(page));
}

// 初期設定テスト
test.describe("初期設定", () => {
  test.only("カスタマイザーでの設定確認", async ({ page }) => {
    const inisialSetting = TEST_CONFIGS.inisialCustomiserSetting;
    const configSp = TEST_CONFIGS.spCustomizerSetting;
    await setHeaderImageDetailSettings(page, configSp, inisialSetting);

    const configPc = TEST_CONFIGS.pcCustomizerSetting;
    await setHeaderImageDetailSettings(page, configPc, inisialSetting);
  });
});
////////////////////////////////////////////////////////
//フロントでテキストの詳細設定を検証する s
////////////////////////////////////////////////////////
test.describe("テキスト設定の検証", () => {
  test.describe("SP環境", () => {
    test.use({
      viewport: TEST_CONFIGS.spCustomizerSetting.viewport,
      userAgent: TEST_CONFIGS.spCustomizerSetting.userAgent,
      extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
    });

    test("テキストの設定確認", async ({ page }) => {
      const config = TEST_CONFIGS.spCustomizerSetting;

      await test.step("フロントページで表示確認", () =>
        verifyText_onFront(
          page,
          config.mainText,
          config.subText,
          config.textPositionTop,
          config.textPositionLeft,
          '"Yu Mincho", 游明朝体, serif',
          "rgb(255, 0, 0)" // pcではrgbで確認
        ));
    });
  });

  test.describe("PC環境", () => {
    test("テキストの設定確認", async ({ page }) => {
      const config = TEST_CONFIGS.pcCustomizerSetting;
      await test.step("フロントページで表示確認", () =>
        verifyText_onFront(
          page,
          config.mainText,
          config.subText,
          config.textPositionTop,
          config.textPositionLeft,
          '"Yu Mincho", 游明朝体, serif',
          "rgb(255, 0, 0)" // pcではrgbで確認
        ));
    });
  });
});
////////////////////////////////////////////////////////
//テキストの詳細設定を検証する e
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
//フロントで画像の詳細設定を検証する
////////////////////////////////////////////////////////
test.describe("フロントで画像の詳細設定を検証する", () => {
  test.describe("SP環境", () => {
    test.beforeAll(async ({ browser }) => {});

    test.use({
      viewport: TEST_CONFIGS.spCustomizerSetting.viewport,
      userAgent: TEST_CONFIGS.spCustomizerSetting.userAgent,
      extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
    });

    test("カスタマイザーで画像、テキストを選択...", async ({ page }) => {
      const config = TEST_CONFIGS.spCustomizerSetting;
      await test.step("フロントページで表示確認", () =>
        verifyImage_onFront(page, config.imagePartialName));
    });
  });

  test.describe("PC環境", () => {
    test("カスタマイザーで画像、テキストを選択...", async ({ page }) => {
      const config = TEST_CONFIGS.pcCustomizerSetting;
      await test.step("フロントページで表示確認", () =>
        verifyImage_onFront(page, config.imagePartialName));
    });
  });
});
