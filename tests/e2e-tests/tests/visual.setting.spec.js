import { test } from "@playwright/test"import { test } from "@playwright/test";

test.describe("動画強制出力", () => {
    test("動画取得の強制テスト（最小版）", async ({ browser, baseURL }, testInfo) => {
        let page;
        let context;
        let video;
        
        // 1. ブラウザコンテキストを手動で作成し、動画設定を強制注入
        //    (storageStateや認証情報は一切含めない)
        context = await browser.newContext({
            video: {
                mode: "on", // 録画を強制的に有効化
                retainOnFailure: true, // 失敗時も保持
            },
            baseURL: baseURL || "https://t2.auroralab-design.com", 
        });
        
        page = await context.newPage();
        
        // 2. 動画オブジェクトにアクセスし、録画開始を強制
        video = page.video();
        if (video) {
            console.log("[DEBUG] Video object found. Forcing initialization.");
            // .path()を呼び出すことで、ffmpegの起動を促す
            await video.path().catch(e => console.error("Video path access failed:", e.message)); 
        }

        try {
            // 3. 動画に記録するための最小限の操作と待機
            //    ページ遷移が失敗しても構いません
            await page.goto("/", { waitUntil: 'domcontentloaded', timeout: 30000 });
            await page.waitForTimeout(5000); // 録画時間を稼ぐための待機

        } catch (e) {
            // エラーが発生しても finally へ進みます
            console.error("Test body encountered an error (will still try to save video):", e);
        } finally {
            // 4. ⭐ 絶体に手動保存を保証 (テストの成否に関わらず実行)
            if (video) {
                const projectName = testInfo.project.name;
                const testTitle = testInfo.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
                const outputPath = `test-results/${projectName}/${testTitle}_ABSOLUTE_MINIMAL.webm`;
                
                console.log(`[DEBUG] Saving video in FINALLY block to: ${outputPath}`);
                
                // ファイル書き込みが完了するまで待つ
                await video.saveAs(outputPath).catch(e => console.error("Video saveAs failed:", e.message));
            }
            
            // ページとコンテキストを閉じる
            if (page) await page.close().catch(e => {});
            if (context) await context.close().catch(e => {});
        }
    });
});