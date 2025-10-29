import { test, expect } from "@playwright/test";

import {
  timeStart,
  logStepTime,
  openCustomizer,
  selSliderEffect,
  saveCustomizer,
  setSiteType,
  ensureCustomizerRoot,
} from "../utils/common";
// 共通設定a
const BASE_URL = "https://wpdev.auroralab-design.com";

// テスト用設定一覧
const TEST_CONFIGS = {
  inisialCustomiserSetting: {
    effectLabel: "フェード",
    siteType: "エレガント",
  },
  spCustomizerSetting: {
    deviceType: "sp",
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
    deviceType: "pc",
    interval: "1",
    mainText: "テストタイトル",
    subText: "これはPlaywrightテストによって入力された説明文です。",
    textPositionTop: "100",
    textPositionLeft: "150",
    textColor: "#ff0000",
    textFont: "yu_mincho",
    imagePartialName: "Firefly-203280",
    imagePartialName_forPcHome2: "Firefly-51159-1.webp",
  },
};

// 共通関数
async function setSliderInterval(page, interval) {
  const intervalInput = page.getByLabel("変更時間間隔（秒）");
  await intervalInput.fill("999999");
  await intervalInput.fill(interval);
}

async function setSliderImage(
  page: Page,
  imagePartialName: string,
  image_delBtnNo: number = 0,
  image_selBtnNo: number = 0
) {
  // 既存画像を削除
  await page.getByRole("button", { name: "削除" }).nth(image_delBtnNo).click();
  await page
    .getByRole("button", { name: "画像を選択" })
    .nth(image_selBtnNo)
    .click();

  // モーダルが表示されるのを待つ
  const mediaModal = page.locator(".attachments-browser");
  await mediaModal.waitFor({ state: "visible", timeout: 15000 });

  // 検索ボックスに入力して検索
  const searchInput = page.locator("#media-search-input");
  await searchInput.fill(imagePartialName);
  await searchInput.press("Enter");

  // 検索結果の最初の画像をクリック
  const targetImage = page
    .locator(`.attachments-browser img[src*="${imagePartialName}"]`)
    .first();
  await targetImage.waitFor({ state: "visible", timeout: 15000 });
  await targetImage.click({ force: true });

  // 選択ボタンを押してモーダルを閉じる
  await page.locator(".media-button-select").click();
  await page
    .locator(".media-modal")
    .waitFor({ state: "hidden", timeout: 15000 });
}

async function setSliderText(page, mainText, subText) {
  await page.getByLabel("スライダーテキスト（メイン）").nth(0).fill(mainText);
  await page.getByLabel("スライダーテキスト（サブ）").nth(0).fill(subText);
  await expect(
    page.getByLabel("スライダーテキスト（メイン）").nth(0)
  ).toHaveValue(mainText);
  await expect(
    page.getByLabel("スライダーテキスト（サブ）").nth(0)
  ).toHaveValue(subText);
}

async function setTextPosition(page, top, left, deviceType = "sp") {
  let text_positionLavel_top = "スライダーテキスト位置（モバイル、上）（px）";
  let text_positionLavel_left = "スライダーテキスト位置（モバイル、左）（px）";
  if (deviceType === "sp") {
    text_positionLavel_top = "スライダーテキスト位置（上）（px）";
    text_positionLavel_left = "スライダーテキスト位置（左）（px）";
  }
  await page.getByLabel(text_positionLavel_top).fill(top);
  await page.getByLabel(text_positionLavel_left).fill(left);
}

async function setTextColor(page, textColor) {
  // 「色を選択」ボタンをクリック → input が表示される
  await page.getByRole("button", { name: "色を選択" }).click();

  const input = page.getByLabel("スライダーテキストカラー");

  await input.fill(textColor);
}

async function setTextFont(page, textFont) {
  // ラベル名から要素を取得
  const label = page.locator("label", {
    hasText: "スライダーテキストフォント",
  });

  // ラベルの for 属性から select の id を取得
  const selectId = await label.getAttribute("for");
  if (!selectId)
    throw new Error(
      "ラベル スライダーテキストフォント に対応する select が見つかりません"
    );

  // select を取得して選択
  const select = page.locator(`#${selectId}`);
  await select.waitFor({ state: "visible" });
  await select.selectOption(textFont);
}

async function verifySliderOnSlide_Home1(
  page,
  imagePartialName,
  expectedCount = 2
) {
  await page.goto("/", { waitUntil: "networkidle" });
  await expect(page.locator(".slider.slide-effect")).toBeVisible();

  // 画像が1秒で切り替わる
  const getTranslateX = async () =>
    await page
      .locator(".slider.slide-effect .slides")
      .evaluate((el) => getComputedStyle(el).transform);

  const firstTransform = await getTranslateX();

  await expect
    .poll(
      async () => {
        const currentTransform = await getTranslateX();
        // transform が変わっていればスライドが動いたと判断
        return currentTransform !== firstTransform;
      },
      {
        timeout: 5000,
        message: "スライドが移動しませんでした",
      }
    )
    .toBe(true);

  //クローンした画像と合わせて２つ存在
  await expect(
    page.locator(`.slider.slide-effect .slide img[src*="${imagePartialName}"]`)
  ).toHaveCount(expectedCount);
}

async function verifySliderOnFade_Home2Sp(page, imagePartialName) {
  await page.goto("/", { waitUntil: "networkidle" });
  await expect(page.locator(".slider.fade-effect")).toBeVisible();

  // 画像が1秒で切り替わる
  const getActiveImageSrc = async () =>
    await page
      .locator(".slider.fade-effect .slide.active img")
      .getAttribute("src");
  const firstSrc = await getActiveImageSrc();
  await expect
    .poll(
      async () => {
        const currentSrc = await getActiveImageSrc();
        return currentSrc !== firstSrc;
      },
      {
        timeout: 3000,
        message: "スライド画像が切り替わりませんでした",
      }
    )
    .toBe(true);

  await expect(
    page.locator(`.slider.fade-effect .slide img[src*="${imagePartialName}"]`)
  ).toHaveCount(1);
}

async function verifySliderOnFade_Front(page, imagePartialName) {
  await page.goto("/", { waitUntil: "networkidle" });
  await expect(page.locator(".slider.fade-effect")).toBeVisible();

  // 画像が1秒で切り替わる
  const getActiveImageSrc = async () =>
    await page
      .locator(".slider.fade-effect .slide.active img")
      .getAttribute("src");
  const firstSrc = await getActiveImageSrc();
  await expect
    .poll(
      async () => {
        const currentSrc = await getActiveImageSrc();
        return currentSrc !== firstSrc;
      },
      {
        timeout: 3000,
        message: "スライド画像が切り替わりませんでした",
      }
    )
    .toBe(true);

  await expect(
    page.locator(`.slider.fade-effect .slide img[src*="${imagePartialName}"]`)
  ).toHaveCount(1);
}

async function verifySliderOnFade_Home2Pc(page, imagePartialName) {
  await page.goto("/", { waitUntil: "networkidle" });
  await expect(page.locator(".slider.fade-effect")).toBeVisible();

  await expect(
    page.locator(`.slider.fade-effect .slide img[src*="${imagePartialName}"]`)
  ).toHaveCount(1);

  // 画像が1秒で切り替わる
  const getActiveImageSrc = async () =>
    await page
      .locator(".slider.fade-effect .slide-center img")
      .getAttribute("src");

  const firstSrc = await getActiveImageSrc();

  let secondSrc;
  await expect
    .poll(
      async () => {
        secondSrc = await getActiveImageSrc();
        return secondSrc !== firstSrc;
      },
      {
        timeout: 3000,
        message: "スライド画像が切り替わりませんでした",
      }
    )
    .toBe(true);

  let thirdSrc;

  await expect
    .poll(
      async () => {
        thirdSrc = await getActiveImageSrc();
        return thirdSrc !== secondSrc;
      },
      {
        timeout: 3000,
        message: "スライド画像が切り替わりませんでした",
      }
    )
    .toBe(true);

  //3つの画像が切り替わることを確認
  expect(firstSrc).not.toBe(secondSrc);
  expect(secondSrc).not.toBe(thirdSrc);
  expect(thirdSrc).not.toBe(firstSrc);

  //想定した画像が含まれることを確認
  expect(firstSrc.includes(imagePartialName)).toBe(true);
}

async function verifyTextDetails(
  page,
  mainText,
  subText,
  top,
  left,
  font,
  fontColor
) {
  await page.goto("/", { waitUntil: "networkidle" });
  await expect(page.locator(".slider.fade-effect")).toBeVisible();

  // テキストと位置確認
  const mainTextLocator = page.locator(".slider .text-overlay h1");
  const subTextLocator = page.locator(".slider .text-overlay h2");
  await expect(mainTextLocator).toHaveText(mainText);
  await expect(subTextLocator).toHaveText(subText);

  const overlay = page.locator(".slider .text-overlay");
  const position = await overlay.evaluate((el) => {
    const style = window.getComputedStyle(el);
    return {
      top: style.top,
      left: style.left,
    };
  });
  expect(position.top).toBe(`${top}px`);
  expect(position.left).toBe(`${left}px`);

  // フォントファミリーチェック
  const fontFamily = await mainTextLocator.evaluate(
    (el) => getComputedStyle(el).fontFamily
  );
  expect(fontFamily).toContain(font); // 部分一致で確認することも可能

  // カラー（文字色）チェック
  const color = await mainTextLocator.evaluate(
    (el) => getComputedStyle(el).color
  );
  expect(color).toBe(fontColor); // 文字色をrgbで指定
}

// 共通テストフロー
async function setSliderDetailSettings(page, config, inisialSetting) {
  await test.step("1. カスタマイザー画面を開く", () => openCustomizer(page));
  //await test.step('3. スライダー設定を開く', () => openSliderSetting(page));
  await test.step("2. スライダーのエフェクト設定", () =>
    selSliderEffect(page, inisialSetting.effectLabel));
  await test.step("3. スライダーの変更間隔を設定", () =>
    setSliderInterval(page, config.interval));
  await test.step("4 スライダー画像を設定", () =>
    setSliderImage(page, config.imagePartialName));
  await test.step("5 スライダーテキストを入力", () =>
    setSliderText(page, config.mainText, config.subText));
  await test.step("6 テキストの表示位置を設定", () =>
    setTextPosition(
      page,
      config.textPositionTop,
      config.textPositionLeft,
      config.deviceType
    ));
  await test.step("6 テキストのカラーを設定", () =>
    setTextColor(page, config.textColor));
  await test.step("6 テキストのフォントを設定", () =>
    setTextFont(page, config.textFont));
  await ensureCustomizerRoot(page);
  await test.step("7.ホームタイプの変更", async () => {
    await setSiteType(page, inisialSetting.siteType);
  });
  await test.step("8. 公開ボタンをクリックして変更を保存", () =>
    saveCustomizer(page));
}

async function set_sliderEffect_and_siteType(page, useEffect, homeType) {
  await test.step("1.カスタマイザー画面を開く", () => openCustomizer(page));
  //await test.step('2.2. スライダー設定を開く', () => openSliderSetting(page));
  await test.step("2. スライダーのエフェクトを設定", () =>
    selSliderEffect(page, useEffect, "1"));
  await ensureCustomizerRoot(page);
  await test.step("4.ホームタイプの変更", async () => {
    await setSiteType(page, homeType);
  });
  await test.step("5. 公開ボタンをクリックして変更を保存", () =>
    saveCustomizer(page));
}

////////////////////////////////////////////////////////
//初期設定
////////////////////////////////////////////////////////
test.describe("初期設定", () => {
  test("カスタマイザーでのテキストの設定確認", async ({ page }) => {
    console.log(
      "[10_slider.spec.ts] ===== START: 初期設定 - カスタマイザーでのテキストの設定確認 ====="
    );
    const inisialSetting = TEST_CONFIGS.inisialCustomiserSetting;
    const configSp = TEST_CONFIGS.spCustomizerSetting;
    await setSliderDetailSettings(page, configSp, inisialSetting);
    const configPc = TEST_CONFIGS.pcCustomizerSetting;
    await setSliderDetailSettings(page, configPc, inisialSetting);
  });
});
////////////////////////////////////////////////////////
//フェード&home1でカスタマイザーでのテキストの詳細設定を検証する s
////////////////////////////////////////////////////////
test.describe("初期設定とテキスト設定の検証", () => {
  test.describe("SP環境", () => {
    test.use({
      viewport: TEST_CONFIGS.spCustomizerSetting.viewport,
      userAgent: TEST_CONFIGS.spCustomizerSetting.userAgent,
      extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
    });

    test("カスタマイザーでのテキストの設定確認", async ({ page }) => {
      console.log(
        "[10_slider.spec.ts] ===== START: 初期設定とテキスト設定の検証 (SP) - カスタマイザーでのテキストの設定確認 ====="
      );
      const config = TEST_CONFIGS.spCustomizerSetting;

      await test.step("フロントページで表示確認", () =>
        verifyTextDetails(
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
    test("カスタマイザーでのテキストの設定確認", async ({ page }) => {
      console.log(
        "[10_slider.spec.ts] ===== START: 初期設定とテキスト設定の検証 (PC) - カスタマイザーでのテキストの設定確認 ====="
      );
      const config = TEST_CONFIGS.pcCustomizerSetting;
      await test.step("フロントページで表示確認", () =>
        verifyTextDetails(
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
//フェード&home1でカスタマイザーでのテキストの詳細設定を検証する e
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
//スライダー(フェード）の動作を検証する s
////////////////////////////////////////////////////////
test.describe("フェード", () => {
  test.describe("home1", () => {
    test.describe("SP環境", () => {
      test.beforeAll(async ({ browser }) => {
        const context = await browser.newContext();
        const page = await context.newPage();

        await set_sliderEffect_and_siteType(page, "フェード", "エレガント");

        await page.close();
        await context.close();
      });

      test.use({
        viewport: TEST_CONFIGS.spCustomizerSetting.viewport,
        userAgent: TEST_CONFIGS.spCustomizerSetting.userAgent,
        extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
      });

      test("カスタマイザーで画像、テキストを選択...", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: フェード > home1 > SP環境 - カスタマイザーで画像、テキストを選択... ====="
        );
        const config = TEST_CONFIGS.spCustomizerSetting;
        await test.step("フロントページで表示確認", () =>
          verifySliderOnFade_Front(page, config.imagePartialName));
      });
    });

    test.describe("PC環境", () => {
      test("カスタマイザーで画像、テキストを選択...", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: フェード > home1 > PC環境 - カスタマイザーで画像、テキストを選択... ====="
        );
        const config = TEST_CONFIGS.pcCustomizerSetting;
        await test.step("フロントページで表示確認", () =>
          verifySliderOnFade_Front(page, config.imagePartialName));
      });
    });
  });
  test.describe("home2", () => {
    test.beforeAll(async ({ browser }) => {
      const context = await browser.newContext();
      const page = await context.newPage();

      await set_sliderEffect_and_siteType(page, "フェード", "ポップ");

      await page.close();
      await context.close();
    });

    test.describe("SP環境", () => {
      test.use({
        viewport: TEST_CONFIGS.spCustomizerSetting.viewport,
        userAgent: TEST_CONFIGS.spCustomizerSetting.userAgent,
        extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
      });

      test("フェード画像切り替え確認", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: フェード > home2 > SP環境 - フェード画像切り替え確認 ====="
        );
        const config = TEST_CONFIGS.spCustomizerSetting;
        await test.step("トップページで表示確認", () =>
          verifySliderOnFade_Home2Sp(page, config.imagePartialName));
      });
    });

    test.describe("PC環境", () => {
      test("フェード画像切り替え確認", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: フェード > home2 > PC環境 - フェード画像切り替え確認 ====="
        );
        const config = TEST_CONFIGS.pcCustomizerSetting;
        await test.step("トップページで表示確認", async () => {
          await verifySliderOnFade_Home2Pc(
            page,
            config.imagePartialName_forPcHome2
          );
        });
      });
    });
  });
});

////////////////////////////////////////////////////////
//スライダー(スライド）の動作を検証する s
////////////////////////////////////////////////////////
test.describe("スライド", () => {
  test.describe("home1", () => {
    test.beforeAll(async ({ browser }) => {
      const context = await browser.newContext();
      const page = await context.newPage();

      await set_sliderEffect_and_siteType(page, "スライド", "エレガント");

      await page.close();
      await context.close();
    });

    test.describe("SP環境", () => {
      test.use({
        viewport: TEST_CONFIGS.spCustomizerSetting.viewport,
        userAgent: TEST_CONFIGS.spCustomizerSetting.userAgent,
        extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
      });

      test("スライド画像切り替え確認", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: スライド > home1 > SP環境 - スライド画像切り替え確認 ====="
        );
        const config = TEST_CONFIGS.spCustomizerSetting;
        await test.step("トップページで表示確認", () =>
          verifySliderOnSlide_Home1(page, config.imagePartialName));
      });
    });

    test.describe("PC環境", () => {
      test("スライド画像切り替え確認", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: スライド > home1 > PC環境 - スライド画像切り替え確認 ====="
        );
        const config = TEST_CONFIGS.pcCustomizerSetting;
        await test.step("トップページで表示確認", async () => {
          await verifySliderOnSlide_Home1(page, config.imagePartialName);
        });
      });
    });
  });

  test.describe("home2", () => {
    test.beforeAll(async ({ browser }) => {
      const context = await browser.newContext();
      const page = await context.newPage();

      await set_sliderEffect_and_siteType(page, "スライド", "ポップ");
      await page.close();
      await context.close();
    });

    test.describe("SP環境", () => {
      test.use({
        viewport: TEST_CONFIGS.spCustomizerSetting.viewport,
        userAgent: TEST_CONFIGS.spCustomizerSetting.userAgent,
        extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
      });

      test("スライド画像切り替え確認", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: スライド > home2 > SP環境 - スライド画像切り替え確認 ====="
        );
        const config = TEST_CONFIGS.spCustomizerSetting;
        await test.step("トップページで表示確認", () =>
          //spはhome2もhome1も同じ動作
          verifySliderOnSlide_Home1(page, config.imagePartialName));
      });
    });

    test.describe("PC環境", () => {
      test("スライド画像切り替え確認", async ({ page }) => {
        console.log(
          "[10_slider.spec.ts] ===== START: スライド > home2 > PC環境 - スライド画像切り替え確認 ====="
        );
        const config = TEST_CONFIGS.pcCustomizerSetting;
        await test.step("トップページで表示確認", async () => {
          //home2もhome1と同じ方法で検証（テストコストの削減優先）
          await verifySliderOnSlide_Home1(
            page,
            config.imagePartialName_forPcHome2,
            3
          );
        });
      });
    });
  });
});

////////////////////////////////////////////////////////
//以降でスライダーの動きを確認 e
////////////////////////////////////////////////////////
