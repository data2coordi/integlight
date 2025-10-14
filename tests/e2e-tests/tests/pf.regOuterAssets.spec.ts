import { test, expect, type Page } from "@playwright/test";
import {
  timeStart,
  logStepTime,
  openCustomizer,
  setFrontType,
  saveCustomizer,
} from "../utils/common";

// 共通関数（カスタマイザーでホームページ表示を設定する）a
async function setHomeDisplayType(page: Page, type: "fixed" | "latest") {
  // 実装例：カスタマイザー画面で選択肢を切り替え
  console.log(`Setting home display type to 開始: ${type}`);
  await test.step(" カスタマイザー画面を開く", () => openCustomizer(page));

  await test.step("フロントページのタイプを設定", () =>
    setFrontType(page, "最新の投稿"));

  await test.step("変更を保存", () => saveCustomizer(page));
  console.log(`Setting home display type to 完了: `);
}

// CSS 検証関数
export async function verifyCssAssets(
  page: Page,
  expectedFrontCss: string[],
  expectedEditorCss: string[],
  expectedDeferredCss: string[]
) {
  await page.goto(page.url(), { waitUntil: "networkidle" });

  // ページ内の全 CSS を取得
  const hrefs: string[] = await page.evaluate(() => {
    const links = document.querySelectorAll("link[rel='stylesheet']");
    const result: string[] = [];
    for (let i = 0; i < links.length; i++) {
      const href = links[i].getAttribute("href");
      if (href) result.push(href);
    }
    return result;
  });

  console.log("読み込まれた CSS:", hrefs);

  // 期待配列に存在する CSS だけ結果配列に追加
  const foundCss: string[] = [];
  for (const expected of expectedFrontCss) {
    for (const href of hrefs) {
      if (href.includes(expected)) {
        foundCss.push(expected);
        break; // 見つけたら次の expected へ
      }
    }
  }

  // 最終的に期待配列と結果配列を比較
  expect(foundCss).toEqual(expectedFrontCss);

  // 遅延 CSS の確認
  for (const deferred of expectedDeferredCss) {
    console.log("Checking deferred CSS:", deferred);
    const deferredCount = await page
      .locator(`link[href*="${deferred}"][onload*="media='all'"]`)
      .count();
    expect(deferredCount).toBeGreaterThan(0);
  }

  // エディタ CSS は別ページで同様に確認可能
}
// データ駆動型テストケース
const TEST_SCENARIOS = [
  {
    id: "TC01",
    name: "フロントページ（固定ページ表示）",
    url: "/",
    homeDisplayType: "fixed",
    expectedFrontCss: [
      "base-style.css",
      "integlight-style.css",
      "front.css",
      "module.css",
    ],
    expectedEditorCss: [
      "base-style.css",
      "integlight-style.css",
      "front.css",
      "module.css",
    ],
    expectedDeferredCss: ["integlight-sp-style"],
  },
  // {
  //   id: "TC02",
  //   name: "ホームページ（最新投稿表示）",
  //   url: "/",
  //   homeDisplayType: "latest",
  //   expectedFrontCss: ["base-style.css", "integlight-style.css", "home.css"],
  //   expectedEditorCss: ["base-style.css", "integlight-style.css", "home.css"],
  //   expectedDeferredCss: ["integlight-sp-style.css", "style.min.css"],
  // },
  // {
  //   id: "TC03",
  //   name: "固定ページ",
  //   url: "/profile/",
  //   homeDisplayType: null,
  //   expectedFrontCss: [
  //     "base-style.css",
  //     "integlight-style.css",
  //     "page.css",
  //     "module.css",
  //     "svg-non-home.css",
  //   ],
  //   expectedEditorCss: [
  //     "base-style.css",
  //     "integlight-style.css",
  //     "page.css",
  //     "module.css",
  //     "svg-non-home.css",
  //   ],
  //   expectedDeferredCss: ["integlight-sp-style.css"],
  // },
  // {
  //   id: "TC04",
  //   name: "投稿ページ",
  //   url: "/test1/",
  //   homeDisplayType: null,
  //   expectedFrontCss: [
  //     "base-style.css",
  //     "integlight-style.css",
  //     "post.css",
  //     "module.css",
  //     "svg-non-home.css",
  //   ],
  //   expectedEditorCss: [
  //     "base-style.css",
  //     "integlight-style.css",
  //     "post.css",
  //     "module.css",
  //     "svg-non-home.css",
  //   ],
  //   expectedDeferredCss: ["integlight-sp-style.css"],
  // },
];

// データ駆動型テストの実行
for (const config of TEST_SCENARIOS) {
  test.describe(config.name, () => {
    let page: Page;
    let context;

    test.beforeAll(async ({ browser }) => {
      context = await browser.newContext();
      page = await context.newPage();

      // フロント/ホームページの表示設定
      if (config.homeDisplayType) {
        await setHomeDisplayType(
          page,
          config.homeDisplayType as "fixed" | "latest"
        );
      }
    });

    test.afterAll(async () => {
      await page.close();
      await context.close();
    });

    test(`CSSアセットが正しく読み込まれている: ${config.id}`, async () => {
      await page.goto(config.url, { waitUntil: "networkidle" });
      await verifyCssAssets(
        page,
        config.expectedFrontCss,
        config.expectedEditorCss,
        config.expectedDeferredCss
      );
    });
  });
}
