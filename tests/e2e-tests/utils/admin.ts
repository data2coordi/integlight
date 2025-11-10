// utils/CustomizerHelper.ts
import { expect, type Page } from "@playwright/test";

// 1. ヘッダー関連操作
export class admin_easySetup {
  constructor(private page: Page) {}

  async apply(value) {
    await this.easySetup(value);
  }

  async easySetup(setting: string) {
    console.log("簡単セットアップのテスト道入");
  }
}
