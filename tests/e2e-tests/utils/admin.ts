// utils/CustomizerHelper.ts
import { expect, type Page } from "@playwright/test";

// 1. ヘッダー関連操作
export class admin_easySetup {
  constructor(private page: Page) {}

  async apply(value) {
    await this.easySetup(value);
  }

  async easySetup(setting: string) {
    console.log("簡単セットアップのテスト開始:", setting);

    // 1. 対象ページに移動
    await this.page.goto(
      "/wp-admin/themes.php?page=integlight-full-debug-setup"
    );

    // 2. 「サンプルコンテンツをセットアップ」ボタンを押下
    const setupButton = this.page.locator(
      "text=サンプルコンテンツをセットアップ"
    );
    await expect(setupButton).toBeVisible();

    // ダイアログ確認のため、beforeイベントで待機
    this.page.once("dialog", async (dialog) => {
      console.log("ダイアログメッセージ:", dialog.message());
      await dialog.accept(); // OKを押す
    });

    await setupButton.click();

    console.log("ボタン押下とダイアログ承認完了");
  }
}
