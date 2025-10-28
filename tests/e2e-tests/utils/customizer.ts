// utils/CustomizerHelper.ts
import { expect, type Page } from "@playwright/test";

// 1. ヘッダー関連操作
export class Customizer_header {
  constructor(private page: Page) {}

  async openHeaderSetting(setting: string) {
    await Customizer_utils.ensureCustomizerRoot(this.page);
    await this.page.getByRole("button", { name: "ヘッダー設定" }).click();
    await this.page
      .getByRole("button", {
        name: "1.「スライダー」タイプまたは「静止画像」タイプを選択",
      })
      .click();
    const effectSelect = this.page.getByRole("combobox", {
      name: "「スライダー」タイプまたは「静止画像」タイプを選択",
    });
    await effectSelect.selectOption({ label: setting });
  }
}
export class Customizer_slider {
  constructor(private page: Page) {}

  async selSliderEffect(effect = "フェード", interval = "5") {
    await Customizer_utils.ensureCustomizerRoot(this.page);
    await this.page.getByRole("button", { name: "ヘッダー設定" }).click();
    await this.page.getByRole("button", { name: "2.スライダー設定" }).click();
    const effectSelect = this.page.getByRole("combobox", {
      name: "エフェクト",
    });
    await effectSelect.selectOption({ label: effect });
    const intervalInput = this.page.getByLabel("変更時間間隔（秒）");
    await intervalInput.fill(interval);
  }
}

// 2. デザイン関連操作
export class Customizer_design {
  constructor(private page: Page) {}

  async setColorSetting(setting: string) {
    await Customizer_utils.ensureCustomizerRoot(this.page);
    await this.page.getByRole("button", { name: "デザイン設定" }).click();
    await this.page.getByRole("button", { name: "配色" }).click();
    const section = this.page.locator(
      "#customize-control-integlight_base_color_setting"
    );
    const checkbox = section.getByLabel(setting);
    if (!(await checkbox.isChecked())) await checkbox.check();
    await expect(checkbox).toBeChecked();
  }
}

// 3. サイト設定関連操作
export class Customizer_siteType {
  constructor(private page: Page) {}

  async setSiteType(siteType = "エレガント") {
    await Customizer_utils.ensureCustomizerRoot(this.page);
    await this.page.getByRole("button", { name: "サイト設定" }).click();
    await this.page.getByRole("button", { name: "サイトタイプ設定" }).click();
    const checkbox = this.page.getByLabel(siteType);
    if (!(await checkbox.isChecked())) await checkbox.check();
    await expect(checkbox).toBeChecked();
  }

  async setFrontType(frontType = "最新の投稿") {
    await Customizer_utils.ensureCustomizerRoot(this.page);
    await this.page.getByRole("button", { name: "サイト設定" }).click();
    await this.page.getByRole("button", { name: "ホームページ設定" }).click();
    const radio = this.page.getByRole("radio", { name: frontType });
    if (!(await radio.isChecked())) await radio.check();
    await expect(radio).toBeChecked();

    if (frontType === "固定ページ") {
      const select = this.page.locator(
        'select[name="_customize-dropdown-pages-page_on_front"]'
      );
      await select.selectOption({ label: "FIREで自由と成長を掴む！" });
      await expect(select).toHaveValue("4210");
    }
  }
}

// 4. 共通ユーティリティ
export class Customizer_utils {
  constructor(private page: Page) {}

  async openCustomizer() {
    await this.page.goto(
      `/wp-admin/customize.php?url=${encodeURIComponent("/")}`,
      {
        waitUntil: "networkidle",
      }
    );
    await expect(this.page.locator(".wp-full-overlay-main")).toBeVisible();
  }

  async saveCustomizer() {
    const saveBtn = this.page.locator("#save");
    if (!(await saveBtn.isEnabled())) return;
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute("value", "公開済み");
    await expect(saveBtn).toBeDisabled();
  }

  static async ensureCustomizerRoot(page: Page) {
    await page.evaluate(() => {
      if (window.wp && window.wp.customize) {
        try {
          window.wp.customize.panel.each((panel) => {
            if (typeof panel.collapse === "function") panel.collapse();
          });
          window.wp.customize.section.each((section) => {
            if (typeof section.collapse === "function") section.collapse();
          });
        } catch {}
      }
    });
    await page.waitForTimeout(200);
  }
}
