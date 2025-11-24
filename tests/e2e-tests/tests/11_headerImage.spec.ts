import { test, expect, BrowserContext } from "@playwright/test";

import { Customizer_manager } from "../utils/customizer";

// テスト用設定一覧
const TEST_CONFIGS = {
  CustomizerSetting: {
    viewport: { width: 375, height: 800 },
    userAgent:
      "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
    mainText: "テストタイトル",
    subText: "これはPlaywrightテストによって入力された説明文です。",
    textPositionTop: "20",
    textPositionLeft: "30",
    textPositionTop_mobile: "5",
    textPositionLeft_mobile: "10",
    textColor: "#ff0000",
    textFont: "yu_mincho",
    imagePartialName: "Firefly-260521",
  },
};

/****設定用 ***********************************/
/****設定用 ***********************************/
/****設定用 ***********************************/
/****設定用 ***********************************/
/****設定用 ***********************************/

// 共通テストフロー
async function setHeaderImageDetailSettings(page, config) {
  const keyValue = {
    headerType: "静止画像",
    headerImageImg: { imageName: config.imagePartialName },
    headerImageText: {
      mainText: config.mainText,
      subText: config.subText,
      textColor: config.textColor,
      textFont: config.textFont,
      textPositionTop: config.textPositionTop,
      textPositionLeft: config.textPositionLeft,
      textPositionTop_mobile: config.textPositionTop_mobile,
      textPositionLeft_mobile: config.textPositionLeft_mobile,
    },
  };
  const cm_manager = new Customizer_manager(page);
  await cm_manager.apply(keyValue);
}

/****検証用 ***********************************/
/****検証用 ***********************************/
/****検証用 ***********************************/
/****検証用 ***********************************/
/****検証用 ***********************************/
/****検証用 ***********************************/
async function verifyText_onFront(
  page,
  mainText,
  subText,
  expectedTop,
  expectedLeft,
  expectedFont, // 部分一致でチェック
  expectedColor
) {
  await page.goto("/", { waitUntil: "networkidle" });
  await expect(page.locator(".header-image")).toBeVisible();

  // ===== テキスト確認 =====
  const mainTextLocator = page.locator(".header-image .text-overlay h2");
  const subTextLocator = page.locator(".header-image .text-overlay h3");

  await expect(mainTextLocator).toHaveText(mainText);
  await expect(subTextLocator).toHaveText(subText);

  // ===== 位置確認 =====
  const overlay = page.locator(".header-image .text-overlay");
  const position = await overlay.evaluate((el) => {
    const style = getComputedStyle(el);
    return { top: style.top, left: style.left };
  });
  expect(position.top).toBe(`${expectedTop}px`);
  expect(position.left).toBe(`${expectedLeft}px`);

  // ===== フォント確認（スライダー方式と同じ） =====
  const fontFamily = await mainTextLocator.evaluate(
    (el) => getComputedStyle(el).fontFamily
  );
  expect(fontFamily.toLowerCase()).toContain(expectedFont.toLowerCase());

  // ===== 色確認（rgb形式） =====
  const color = await mainTextLocator.evaluate(
    (el) => getComputedStyle(el).color
  );
  expect(color).toBe(expectedColor);
}

async function verifyImage_onFront(page, imagePartialName: string) {
  // フロントページを開く
  await page.goto("/", { waitUntil: "networkidle" });

  // 画像要素の取得
  const imageLocator = page.locator(`.header-image img.topImage`);

  // 表示確認
  await expect(imageLocator).toBeVisible({ timeout: 10000 });

  // src属性に指定した部分文字列が含まれていることを確認
  const src = await imageLocator.getAttribute("src");
  if (!src) {
    throw new Error("画像の src 属性が取得できませんでした");
  }

  expect(src).toContain(imagePartialName);
}

/* TEST実行****************/
/* TEST実行****************/
/* TEST実行****************/
/* TEST実行****************/
/* TEST実行****************/
////////////////////////////////////////////////////////
// 初期設定テスト
////////////////////////////////////////////////////////
test.describe("初期設定", () => {
  test("カスタマイザーでの設定確認", async ({ page }) => {
    console.log(
      "[11_headerImage.spec.ts] ===== START: 初期設定 - カスタマイザーでの設定確認 ====="
    );
    const config = TEST_CONFIGS.CustomizerSetting;
    await setHeaderImageDetailSettings(page, config);
  });
});

const SITE_TYPES = ["エレガント", "ポップ"];

for (const siteType of SITE_TYPES) {
  let context: BrowserContext;
  let page: Page;

  test.describe.serial(`サイトタイプ: ${siteType}`, () => {
    test.beforeAll(async ({ browser }) => {
      // 自前で context と page を作成
      context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        userAgent: "",
      });
      page = await context.newPage();

      const keyValue = {
        siteType: siteType,
      };
      const cm_manager = new Customizer_manager(page);
      await cm_manager.apply(keyValue);
    });

    test.afterAll(async () => {
      await context.close(); // context を閉じる
    });

    ////////////////////////////////////////////////////////
    //フロントでテキストの詳細設定を検証する s
    ////////////////////////////////////////////////////////
    test.describe(`テキスト設定の検証:${siteType}`, () => {
      test.describe("SP環境", () => {
        test.use({
          viewport: TEST_CONFIGS.CustomizerSetting.viewport,
          userAgent: TEST_CONFIGS.CustomizerSetting.userAgent,
          extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
        });

        test("テキストの設定確認", async ({ page }) => {
          console.log(
            `[11_headerImage.spec.ts] ===== START: サイトタイプ: ${siteType} > テキスト設定の検証:${siteType} > SP環境 - テキストの設定確認 =====`
          );
          const config = TEST_CONFIGS.CustomizerSetting;

          await test.step("ホームで表示確認", () =>
            verifyText_onFront(
              page,
              config.mainText,
              config.subText,
              config.textPositionTop_mobile,
              config.textPositionLeft_mobile,
              '"Yu Mincho", 游明朝体, serif',
              "rgb(255, 0, 0)" // rgbで確認
            ));
        });
      });

      test.describe("PC環境", () => {
        test("テキストの設定確認", async ({ page }) => {
          console.log(
            `[11_headerImage.spec.ts] ===== START: サイトタイプ: ${siteType} > テキスト設定の検証:${siteType} > PC環境 - テキストの設定確認 =====`
          );
          const config = TEST_CONFIGS.CustomizerSetting;
          await test.step("ホームで表示確認", () =>
            verifyText_onFront(
              page,
              config.mainText,
              config.subText,
              config.textPositionTop,
              config.textPositionLeft,
              '"Yu Mincho", 游明朝体, serif',
              "rgb(255, 0, 0)" // rgbで確認
            ));
        });
      });
    });
    ////////////////////////////////////////////////////////
    //フロントで画像の詳細設定を検証する
    ////////////////////////////////////////////////////////

    test.describe(`フロントでヘッダー画像の検証:${siteType}`, () => {
      test.describe("SP環境", () => {
        test.use({
          viewport: TEST_CONFIGS.CustomizerSetting.viewport,
          userAgent: TEST_CONFIGS.CustomizerSetting.userAgent,
          extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
        });

        test("ホームで画像の表示確認", async ({ page }) => {
          console.log(
            `[11_headerImage.spec.ts] ===== START: サイトタイプ: ${siteType} > フロントでヘッダー画像の検証:${siteType} > SP環境 - ホームで画像の表示確認 =====`
          );
          const config = TEST_CONFIGS.CustomizerSetting;
          await test.step("ホームで表示確認ステップ", () =>
            verifyImage_onFront(page, config.imagePartialName));
        });
      });

      test.describe("PC環境", () => {
        test("ホームで画像の表示確認", async ({ page }) => {
          console.log(
            `[11_headerImage.spec.ts] ===== START: サイトタイプ: ${siteType} > フロントでヘッダー画像の検証:${siteType} > PC環境 - ホームで画像の表示確認 =====`
          );
          const config = TEST_CONFIGS.CustomizerSetting;
          await test.step("ホームで表示確認ステップ", () =>
            verifyImage_onFront(page, config.imagePartialName));
        });
      });
    });
  });
}
