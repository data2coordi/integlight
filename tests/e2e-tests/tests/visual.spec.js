import { test, expect } from "@playwright/test";
// ======= 共通関数 =======

// ======= 設定 =======

const devices = [
  {
    name: "PC",
    use: {
      viewport: { width: 1920, height: 1080 },
      userAgent:
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137 Safari/537.36",
    },
  },
  {
    name: "Mobile",
    use: {
      viewport: { width: 375, height: 800 },
      userAgent:
        "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
    },
  },
];

// ======= テスト展開 =======
test.describe.parallel("ビジュアルテスト", () => {
  for (const device of devices) {
    test.describe(`${device.name}`, () => {
      test.use(device.use);

      test("", async ({ page }) => {
        const { pages } = test.info().project.use;
        // プロジェクト名を取得
        const projectName = test.info().project.name;
        // --- デバッグ出力 ---
        console.log("✅ Loaded pages from config:");
        console.table(
          pages.map((p, i) => ({ No: i + 1, name: p.name, url: p.url }))
        );

        for (const { name, url } of pages) {
          await test.step(`ページ：${name}`, async () => {
            await page.goto(url);
            await page.waitForLoadState("networkidle");
          });

          const options = {
            maxDiffPixelRatio: 0.03, // 人間の目でわからないレベル
            threshold: 0.03,
          };
          // 変更点: expect を expect.soft に変更
          await expect
            .soft(page)
            .toHaveScreenshot(`${device.name}-${name}.png`, {
              fullPage: true,
              timeout: 100000,
              ...options,
            });

          console.log(
            `✨ **完了**: [プロジェクト: ${projectName}] [デバイス: ${device.name}] のページ「${name}」`
          );
        }
      });
    });
    //break;
  }
});
//break;
