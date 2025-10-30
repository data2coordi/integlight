import { test, expect, type Page } from "@playwright/test";
import {
  timeStart,
  logStepTime,
  openCustomizer,
  openHeaderSetting,
  selSliderEffect,
  saveCustomizer,
  setSiteType,
  ensureCustomizerRoot,
} from "../utils/common";

import { Customizer_manager } from "../utils/customizer";

// テスト設定を統合し、階層的な構造にする
const TEST_SCENARIOS = {
  ヘッダーなし: {
    spHome1: {
      viewport: { width: 375, height: 800 },
      userAgent:
        "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      siteType: "エレガント",
      headerType: "なし",
      headCt: 0,
      bodyCt: 1,
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
    pcHome1: {
      siteType: "エレガント",
      headerType: "なし",
      headCt: 0,
      bodyCt: 3,
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
    spHome2: {
      viewport: { width: 375, height: 800 },
      userAgent:
        "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      siteType: "ポップ",
      headerType: "なし",
      headCt: 0,
      bodyCt: 2,
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
    pcHome2: {
      siteType: "ポップ",
      headerType: "なし",
      headCt: 0,
      bodyCt: 4,
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
  },
  ヘッダーあり: {
    spHome1: {
      viewport: { width: 375, height: 800 },
      userAgent:
        "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      siteType: "エレガント",
      headerType: "スライダー",
      headCt: 1,
      bodyCt: 0, // 優先読み込みすべきボディ画像がないことを意味する
      headSelector: ".slider img",
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
    pcHome1: {
      siteType: "エレガント",
      headerType: "スライダー",
      headCt: 1,
      bodyCt: 0, // 優先読み込みすべきボディ画像がないことを意味する
      headSelector: ".slider img",
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
    spHome2: {
      viewport: { width: 375, height: 800 },
      userAgent:
        "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      siteType: "ポップ",
      headerType: "スライダー",
      headCt: 1,
      bodyCt: 1,
      headSelector: ".slider img",
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
    pcHome2: {
      siteType: "ポップ",
      headerType: "スライダー",
      headCt: 3,
      bodyCt: 2,
      headSelector: ".slider img",
      bodySelector: ".post-grid .grid-item .post-thumbnail img",
    },
  },
};

//////////////////////////////外出し候補end

async function verifyImageAttributes(
  page: Page,
  selector: string,
  priorityCount = 0
) {
  // timeStart('verifyImageAttributes');

  await page.goto("/", { waitUntil: "networkidle" });
  const images = page.locator(selector);
  const count = await images.count();

  //console.log(`画像の総数: ${count} 優先読み込みの画像数：${priorityCount}`);

  for (let i = 0; i < count; i++) {
    const img = images.nth(i);
    const src = (await img.getAttribute("src")) || "(no src)";
    const fetchpriority = await img.getAttribute("fetchpriority");
    const loading = await img.getAttribute("loading");

    const filename = src
      ? src
          .split("/")
          .pop()!
          .split("?")[0]
          .replace(/-\d+x\d+(?=\.[^.]+$)/, "")
      : "(no src)";
    // console.log(`[${i + 1}枚目:] fetchpriority="${fetchpriority}" | loading="${loading} | src:${filename}`);

    if (i < priorityCount) {
      if (fetchpriority !== "high") {
        throw new Error(
          `[${
            i + 1
          }枚目:${src}] fetchpriority expected="high" actual="${fetchpriority}"`
        );
      }
      if (loading === "lazy") {
        throw new Error(
          `[${
            i + 1
          }枚目:${src}] loading should NOT be "lazy", actual="${loading}"`
        );
      }
    } else {
      if (loading !== "lazy") {
        throw new Error(
          `[${i + 1}枚目:${src}] loading expected="lazy" actual="${loading}"`
        );
      }
      if (fetchpriority === "high") {
        throw new Error(
          `[${
            i + 1
          }枚目:${src}] fetchpriority should NOT be "high", actual="${fetchpriority}"`
        );
      }
    }
  }

  //logStepTime('verifyImageAttributes');
}

// 共通テストフロー
async function runCustomizerFlow(page: Page, config: any) {
  // ログイン処理はauth.setup.tsで完了済み

  //timeStart('openCustomizer_1');
  await test.step("2. カスタマイザー画面を開く", () => openCustomizer(page));
  //logStepTime('openCustomizer_1');

  //timeStart('openHeaderSetting');
  await test.step("3. ヘッダー有無を設定", () =>
    openHeaderSetting(page, config.headerType));
  //logStepTime('openHeaderSetting');

  //timeStart('CustomizerRoot_1');
  await ensureCustomizerRoot(page);
  //logStepTime('CustomizerRoot_1');

  if (config.headerType === "スライダー") {
    //timeStart('sliderSettings');
    await test.step("8. スライダー設定", async () => {
      await selSliderEffect(page);
    });
    //logStepTime('sliderSettings');

    //timeStart('CustomizerRoot_2');
    await ensureCustomizerRoot(page);
    //logStepTime('CustomizerRoot_2');
  }

  //timeStart('setSiteType');
  await test.step("6. ホームタイプの変更", () =>
    setSiteType(page, config.siteType));
  //logStepTime('setSiteType');

  //timeStart('saveCustomizer');
  await test.step("7. 変更を保存", () => saveCustomizer(page));
  //logStepTime('saveCustomizer');

  let keyValue = {
    siteType: config.siteType,
    headerType: config.headerType,
  };

  if (config.headerType === "スライダー") {
    keyValue = {
      ...keyValue,
      sliderType: {}, //警告が出るが使える。
    };
  }
  const cm_manager = new Customizer_manager(page);
  await cm_manager.apply(keyValue);
}

// データ駆動型テストの実行
for (const [headerGroup, scenarios] of Object.entries(TEST_SCENARIOS)) {
  test.describe(headerGroup, () => {
    for (const [testCaseName, config] of Object.entries(scenarios)) {
      const isSP = testCaseName.startsWith("sp");
      const deviceDesc = isSP ? "SP環境" : "PC環境";

      test.describe(`${testCaseName} (${deviceDesc})`, () => {
        let page: Page;
        let context;

        test.beforeAll(async ({ browser }) => {
          context = await browser.newContext();
          page = await context.newPage();
          if (isSP) {
            await runCustomizerFlow(page, config);
          }
        });

        test.afterAll(async () => {
          await page.close();
          await context.close();
        });

        if (isSP) {
          test.use({
            viewport: (config as any).viewport,
            userAgent: (config as any).userAgent,
            extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
          });
        }

        test("画像ロード属性を確認", async () => {
          console.log(
            `[07_pf.image.home.spec.ts] ===== START: ${headerGroup} - ${testCaseName} (${deviceDesc}) - 画像ロード属性を確認 =====`
          );
          if (config.headCt > 0 && config.headSelector) {
            //console.log(`@@@@@@@@@@ヘッダー画像のチェック: ${config.headSelector}`);
            await test.step("ヘッダー画像の属性チェック", () =>
              verifyImageAttributes(page, config.headSelector, config.headCt));
          }
          if (config.bodySelector) {
            //console.log(`@@@@@@@@@@ボディ画像のチェック: ${config.bodySelector}`);
            await test.step("ボディ画像の属性チェック", () =>
              verifyImageAttributes(page, config.bodySelector, config.bodyCt));
          }
        });
      });
    }
  });
}
