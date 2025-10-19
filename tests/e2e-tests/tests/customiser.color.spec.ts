import { test, expect, type Page } from "@playwright/test";
import {
  timeStart,
  logStepTime,
  openCustomizer,
  saveCustomizer,
  setColorSetting,
} from "../utils/common";

// 共通関数（カスタマイザーでホームページ表示を設定する）a
async function setColorPattern(page: Page, colorType: string) {
  // 実装例：カスタマイザー画面で選択肢を切り替え
  console.log(`Setting color pattern  to 開始: ${colorType}`);
  await test.step(" カスタマイザー画面を開く", () => openCustomizer(page));

  await test.step("カラー設定の変更", () => setColorSetting(page, colorType));

  await test.step("変更を保存", () => saveCustomizer(page));
  console.log(`Setting color pattern to 完了: ${colorType}`);
}

// CSS 検証関数
export async function verifyCssAssets(page: Page, expectedFrontCss: string[]) {
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
  for (const expected of expectedFrontCss) {
    let found = false;

    for (const href of hrefs) {
      if (href.includes(expected)) {
        found = true;
        break; // 見つかったので次の expected へ
      }
    }

    // 含まれていない場合は即テスト失敗
    expect(found, `CSSが見つかりません: ${expected}`).toBeTruthy();
  }

  // エディタ CSS は別ページで同様に確認可能
}
// データ駆動型テストケース
const TEST_SCENARIOS = [
  {
    id: "TC01",
    name: "ホームページ",
    url: "/",
    colorType: "パープル",
    expectedFrontCss: ["all.upd.color-pattern5"],
  },
  {
    id: "TC02",
    name: "固定ページ",
    url: "/profile/",
    colorType: "緑",
    expectedFrontCss: ["all.upd.color-pattern3"],
  },
  {
    id: "TC03",
    name: "投稿ページ",
    url: "/test1/",
    colorType: "ネイビー",
    expectedFrontCss: ["all.upd.color-pattern8"],
  },
];

// データ駆動型テストの実行
for (const config of TEST_SCENARIOS) {
  test.describe(config.name, () => {
    let page: Page;
    let context;

    test.beforeAll(async ({ browser }) => {
      context = await browser.newContext();
      page = await context.newPage();

      await setColorPattern(page, config.colorType);
    });

    test.afterAll(async () => {
      await page.close();
      await context.close();
    });

    test(`CSSアセットが正しく読み込まれている: ${config.id}`, async () => {
      await page.goto(config.url, { waitUntil: "networkidle" });
      await verifyCssAssets(page, config.expectedFrontCss);
    });
  });
}
