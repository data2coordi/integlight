import { test, expect } from "@playwright/test";

// 共通設定a
import { Customizer_manager } from "../utils/customizer";

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
    deviceType: "sp",
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
    deviceType: "pc",
    textPositionTop: "100",
    textPositionLeft: "150",
    textColor: "#ff0000",
    textFont: "yu_mincho",
    imagePartialName: "Firefly-203280",
    imagePartialName_forPcHome2: "Firefly-51159-1.webp",
  },
};

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
  const cm_manager = new Customizer_manager(page);
  const keyValue = {
    siteType: inisialSetting.siteType,
    sliderType: {
      effect: inisialSetting.effectLabel,
      interval: config.interval,
    },
    sliderImg: { imagePartialName: config.imagePartialName },
    sliderText: {
      mainText: config.mainText,
      subText: config.subText,
      top: config.textPositionTop,
      left: config.textPositionLeft,
      deviceType: config.deviceType,
      textColor: config.textColor,
      textFont: config.textFont,
    },
  };
  await cm_manager.apply(keyValue);
}

async function set_sliderEffect_and_siteType(
  page,
  useEffect,
  siteType,
  interval
) {
  const keyValue = {
    siteType: siteType,
    sliderType: { effect: useEffect, interval: interval },
  };

  const cm_manager = new Customizer_manager(page);
  await cm_manager.apply(keyValue);
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

        await set_sliderEffect_and_siteType(
          page,
          "フェード",
          "エレガント",
          "1"
        );

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

      await set_sliderEffect_and_siteType(page, "フェード", "ポップ", "1");

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

      await set_sliderEffect_and_siteType(page, "スライド", "エレガント", "1");

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

      await set_sliderEffect_and_siteType(page, "スライド", "ポップ", "1");
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
