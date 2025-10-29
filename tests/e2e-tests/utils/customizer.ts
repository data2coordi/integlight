// utils/CustomizerHelper.ts
import { expect, type Page } from "@playwright/test";

// 1. ヘッダー関連操作
export class Customizer_header {
  constructor(private page: Page) {}

  async apply(value) {
    await this.openHeaderSetting(value);
  }

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

  async apply(config: { effect?: string; interval?: string }) {
    const { effect = "フェード", interval = "5" } = config;
    await this.selSliderEffect(effect, interval);
  }
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

  async apply(value) {
    await this.setSiteType(value);
  }
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

export class Customizer_manager {
  constructor(page) {
    this.page = page;
    this.utils = new Customizer_utils(page);

    this.handlers = {
      siteType: new Customizer_siteType(page),
      headerType: new Customizer_header(page),
      sliderType: new Customizer_slider(page),
      // 追加クラスもここに登録するだけ
    };
  }

  /**
   * 全体設定を適用
   * @param {object} keyValue 
   * 例：
      {
          testid: 'elegant_slider',
          siteType: 'エレガント',
          headerType: 'スライダー',
          sliderType: { effect: 'スライド', interval: '60' }
      }
   */
  async apply(keyValue) {
    console.log("=== Customizer apply start ===");
    console.log(keyValue);

    await this.utils.openCustomizer();

    for (const [key, value] of Object.entries(keyValue)) {
      console.log(`--- Applying ${key}: ${JSON.stringify(value)} ---`);
      if (!value || key === "testid") continue;
      const handler = this.handlers[key];
      await handler.apply(value);
    }

    await this.utils.saveCustomizer();
    console.log("=== Customizer apply done ===");
  }
}

//
//

/*
Customizer_header.js
export class Customizer_header {
  constructor(page) {
    this.page = page;
  }

  async apply(value) {
    await this.openHeaderSetting(value);
  }

  async openHeaderSetting(headerType) {
    console.log(`✅ headerType set to: ${headerType}`);
    // await this.page.locator(...).click();
  }
}
*/

//実行例
/*
const config = {
  testid: "pop_slider",
  siteType: "ポップ",
  headerType: "スライダー",
  sliderType: { effect: "スライド", interval: "60" },
};

const cm = new CustomizerManager(page);
await cm.apply(config);
*/
