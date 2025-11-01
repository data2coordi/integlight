import { test, expect } from "@playwright/test";
import { Customizer_manager } from "../utils/customizer";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ browser }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`);

    let keyValue = testInfo.project.use.keyValue;
    console.log(`@@@@@keyValue@@@@@: ${JSON.stringify(keyValue)}`);

    const page = await browser.newPage();
    expect(page).toBeDefined();

    // ===============================================
    // ⭐ 動画録画の強制的な初期化を試みる (ffmpegの欠落に対応)
    //    テスト操作の前に実行することで、操作を記録させる
    // ===============================================
    const video = page.video();
    let videoPath;
    if (video) {
      console.log(
        "[DEBUG] Forcefully accessing video object to trigger initialization."
      );
      try {
        // video.path() へのアクセスが、Playwrightに録画セッションの開始を促すことを期待
        // これにより、ffmpegが起動する可能性がある
        videoPath = await video.path();
        console.log(
          `[DEBUG] Video Path after forced access (initiation check): ${videoPath}`
        );
      } catch (e) {
        console.error(
          "動画オブジェクトの初期化チェックに失敗しました。",
          e.message
        );
      }
    }
    // ===============================================

    const cm_manager = new Customizer_manager(page);
    await cm_manager.apply(keyValue); // ここから実際の操作が記録されるはず

    await page.waitForTimeout(2000);

    // ===============================================
    // ⭐ 動画の手動保存を保証 (動画が自動保存されない問題に対応)
    // ===============================================
    if (video && videoPath) {
      const projectName = testInfo.project.name;
      // ファイル名として安全な形式にテスト名を変換
      const testTitle = testInfo.title
        .replace(/[^a-z0-9]/gi, "_")
        .toLowerCase();

      // Playwrightの標準的な保存先に合わせてパスを構成
      const outputPath = `test-results/${projectName}/${testTitle}.webm`;

      console.log(`[DEBUG] Manually saving video to: ${outputPath}`);

      // 動画のファイル書き込みが確実に完了するまで待つ
      await video.saveAs(outputPath);
    }
    // ===============================================

    await page.close();
  });
});
