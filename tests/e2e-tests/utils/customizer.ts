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

export class Customizer_slider_img {
  constructor(private page: Page) {}

  async apply(config: {
    imagePartialName?: string;
    image_delBtnNo?: number;
    image_selBtnNo?: number;
  }) {
    const {
      imagePartialName = "",
      image_delBtnNo = 0,
      image_selBtnNo = 0,
    } = config;
    await this.setSliderImage(imagePartialName, image_delBtnNo, image_selBtnNo);
  }

  async setSliderImage(
    imagePartialName: string,
    image_delBtnNo: number = 0,
    image_selBtnNo: number = 0
  ) {
    await Customizer_utils.ensureCustomizerRoot(this.page);
    await this.page.getByRole("button", { name: "ヘッダー設定" }).click();
    await this.page.getByRole("button", { name: "2.スライダー設定" }).click();

    // 既存画像を削除
    await this.page
      .getByRole("button", { name: "削除" })
      .nth(image_delBtnNo)
      .click();
    await this.page
      .getByRole("button", { name: "画像を選択" })
      .nth(image_selBtnNo)
      .click();

    // モーダルが表示されるのを待つ
    const mediaModal = this.page.locator(".attachments-browser");
    await mediaModal.waitFor({ state: "visible", timeout: 15000 });

    // 検索ボックスに入力して検索
    const searchInput = this.page.locator("#media-search-input");
    await searchInput.fill(imagePartialName);
    await searchInput.press("Enter");

    // 検索結果の最初の画像をクリック
    const targetImage = this.page
      .locator(`.attachments-browser img[src*="${imagePartialName}"]`)
      .first();
    await targetImage.waitFor({ state: "visible", timeout: 15000 });
    await targetImage.click({ force: true });

    // 選択ボタンを押してモーダルを閉じる
    await this.page.locator(".media-button-select").click();
    await this.page
      .locator(".media-modal")
      .waitFor({ state: "hidden", timeout: 15000 });
  }
}

export class Customizer_slider_text {
  constructor(private page: Page) {}
  async apply(config: {
    mainText?: string;
    subText?: string;
    top?: string;
    left?: string;
    deviceType?: string;
    textColor?: string;
    textFont?: string;
  }) {
    const {
      mainText = "Main Text dummy",
      subText = "Sub Text dummy",
      top = "1",
      left = "1",
      deviceType = "pc",
      textColor = "#000000",
      textFont = "Arial",
    } = config;

    await Customizer_utils.ensureCustomizerRoot(this.page);
    await this.page.getByRole("button", { name: "ヘッダー設定" }).click();
    await this.page.getByRole("button", { name: "2.スライダー設定" }).click();

    await this.setSliderText(mainText, subText);
    await this.setTextPosition(top, left, deviceType);

    await this.setTextColor(textColor);
    await this.setTextFont(textFont);
  }

  async setSliderText(mainText, subText) {
    await this.page
      .getByLabel("スライダーテキスト（メイン）")
      .nth(0)
      .fill(mainText);
    await this.page
      .getByLabel("スライダーテキスト（サブ）")
      .nth(0)
      .fill(subText);
    await expect(
      this.page.getByLabel("スライダーテキスト（メイン）").nth(0)
    ).toHaveValue(mainText);
    await expect(
      this.page.getByLabel("スライダーテキスト（サブ）").nth(0)
    ).toHaveValue(subText);
  }

  async setTextPosition(top, left, deviceType = "sp") {
    let text_positionLavel_top = "スライダーテキスト位置（モバイル、上）（px）";
    let text_positionLavel_left =
      "スライダーテキスト位置（モバイル、左）（px）";
    if (deviceType === "pc") {
      text_positionLavel_top = "スライダーテキスト位置（上）（px）";
      text_positionLavel_left = "スライダーテキスト位置（左）（px）";
    }
    await this.page.getByLabel(text_positionLavel_top).fill(top);
    await this.page.getByLabel(text_positionLavel_left).fill(left);
  }

  async setTextColor(textColor) {
    // 「色を選択」ボタンをクリック → input が表示される
    await this.page.getByRole("button", { name: "色を選択" }).click();

    const input = this.page.getByLabel("スライダーテキストカラー");

    await input.fill(textColor);
  }

  async setTextFont(textFont) {
    // ラベル名から要素を取得
    const label = this.page.locator("label", {
      hasText: "スライダーテキストフォント",
    });

    // ラベルの for 属性から select の id を取得
    const selectId = await label.getAttribute("for");
    if (!selectId)
      throw new Error(
        "ラベル スライダーテキストフォント に対応する select が見つかりません"
      );

    // select を取得して選択
    const select = this.page.locator(`#${selectId}`);
    await select.waitFor({ state: "visible" });
    await select.selectOption(textFont);
  }
}

// 2. デザイン関連操作
export class Customizer_design {
  constructor(private page: Page) {}

  async apply(value) {
    await this.setColorSetting(value);
  }

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
      sliderImg: new Customizer_slider_img(page),
      sliderText: new Customizer_slider_text(page),
      colorType: new Customizer_design(page),
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
const keyValue = {
  testid: "pop_slider",
  siteType: "ポップ",
  headerType: "スライダー",
  sliderType: { effect: "スライド", interval: "60" },
};

const cm = new CustomizerManager(page);
await cm.apply(keyValue);
*/
