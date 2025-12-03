import { test, expect, type Page } from "@playwright/test";

import { Customizer_manager } from "../utils/customizer";

// 共通関数（カスタマイザーでホームページ表示を設定する）a
async function setHomeDisplayType(page: Page, frontType: string) {
  let keyValue = {
    siteType: "ポップ",
    frontType: frontType,
  };

  const cm_manager = new Customizer_manager(page);
  await cm_manager.apply(keyValue);
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

  //console.log("読み込まれた CSS:", hrefs);

  // 期待配列に存在する CSS だけ結果配列に追加
  let foundCss: string[] = [];
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

  // 期待配列に存在する CSS だけ結果配列に追加
  foundCss = [];
  for (const expected of expectedEditorCss) {
    for (const href of hrefs) {
      if (href.includes(expected)) {
        foundCss.push(expected);
        break; // 見つけたら次の expected へ
      }
    }
  }

  // 最終的に期待配列と結果配列を比較
  expect(foundCss).toEqual(expectedEditorCss);

  // 遅延 CSS の確認
  for (const deferred of expectedDeferredCss) {
    //console.log("Checking deferred CSS:", deferred);
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
    homeDisplayType: "固定ページ",
    expectedFrontCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.front.css",
      "all.parts.module-forTheme.css",
      "all.parts.module-forBlockItem.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedEditorCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.front.css",
      "all.parts.module-forTheme.css",
      "all.parts.module-forBlockItem.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedDeferredCss: ["all.upd.color-pattern8"],
  },
  {
    id: "TC02",
    name: "ホームページ（最新投稿表示）",
    url: "/",
    homeDisplayType: "最新の投稿",
    expectedFrontCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.home.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedEditorCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.home.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedDeferredCss: ["all.upd.color-pattern8"],
  },
  {
    id: "TC03",
    name: "固定ページ",
    url: "/profile/",
    homeDisplayType: null,
    expectedFrontCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.page.css",
      "all.parts.module-forTheme.css",
      "all.parts.module-forBlockItem.css",
      "all.sp.svg-non-home.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedEditorCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.page.css",
      "all.parts.module-forTheme.css",
      "all.parts.module-forBlockItem.css",
      "all.sp.svg-non-home.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedDeferredCss: ["all.upd.color-pattern8"],
  },
  {
    id: "TC04",
    name: "投稿ページ",
    url: "/test1/",
    homeDisplayType: null,
    expectedFrontCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.post.css",
      "all.parts.module-forTheme.css",
      "all.parts.module-forBlockItem.css",
      "all.sp.svg-non-home.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedEditorCss: [
      "all.cmn.layout.css",
      "all.cmn.nonLayout0.css",
      "all.cmn.nonLayout1.css",
      "all.sp.menu.css",
      "all.helper.css",
      "page.post.css",
      "all.parts.module-forTheme.css",
      "all.parts.module-forBlockItem.css",
      "all.sp.svg-non-home.css",
      "all.upd.color-pattern8",
      "all.upd.site-type2.css",
    ],
    expectedDeferredCss: ["all.upd.color-pattern8"],
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

      // フロント/ホームページの表示設定
      if (config.homeDisplayType) {
        await setHomeDisplayType(page, config.homeDisplayType);
      }
    });

    test.afterAll(async () => {
      await page.close();
      await context.close();
    });

    test(`CSSアセットが正しく読み込まれている: ${config.id}`, async () => {
      console.log(
        `[09_pf.regOuterAssets.spec.ts] ===== START: ${config.name} - CSSアセットが正しく読み込まれている: ${config.id} =====`
      );
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

// ==============================
// JSファイルの確認テスト
// ==============================

test.describe("JSファイルが正しく読み込まれているか", () => {
  let page: Page;
  let context;

  // 確認したいファイル名
  const targetJsFiles = ["slider.js", "navigation.js", "loadmore.js"];

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext();
    page = await context.newPage();
    await page.goto("/", { waitUntil: "networkidle" });
  });

  test.afterAll(async () => {
    await page.close();
    await context.close();
  });

  test("各JSファイルが<body>内にあり、defer属性があることを確認", async () => {
    console.log(
      "[09_pf.regOuterAssets.spec.ts] ===== START: JSファイルが正しく読み込まれているか - 各JSファイルが<body>内にあり、defer属性があることを確認 ====="
    );
    // scriptタグの情報をブラウザ内で取得
    const scripts = await page.evaluate(() => {
      const allScripts = document.querySelectorAll("script[src]");
      const result = [];

      for (const el of allScripts) {
        const info = {
          src: el.getAttribute("src"),
          isInBody: document.body.contains(el),
          hasDefer: el.hasAttribute("defer"),
        };
        result.push(info);
      }

      return result;
    });

    //console.log("検出されたスクリプト一覧:", scripts);

    // 1つずつ確認
    for (const fileName of targetJsFiles) {
      let found = null;

      // scripts配列の中から一致するものを探す
      for (const script of scripts) {
        if (script.src && script.src.indexOf(fileName) !== -1) {
          found = script;
          break;
        }
      }

      // ファイルが見つからなかった場合
      expect(found, `${fileName} が見つかること`).toBeTruthy();

      if (found) {
        // body内にあること
        expect(found.isInBody, `${fileName} は<body>内にあること`).toBeTruthy();

        // defer属性があること
        expect(
          found.hasDefer,
          `${fileName} に defer 属性があること`
        ).toBeTruthy();
      }
    }
  });
});
