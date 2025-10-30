// utils/CustomizerHelper.ts
import { expect, type Page } from "@playwright/test";

// -------------------- Timer --------------------
export class Timer {
  private timers = new Map<string, number>();

  start(stepName: string) {
    this.timers.set(stepName, Date.now());
  }

  log(stepName: string) {
    const startTime = this.timers.get(stepName);
    if (startTime) {
      console.log(
        `[Timer] Step "${stepName}" took ${Date.now() - startTime}ms`
      );
    }
  }
}

// -------------------- Debugger --------------------
export class Debugger {
  constructor(private page: Page) {}

  async showCodeOverlay(code: string, duration = 3000) {
    await this.page.evaluate((code) => {
      const existing = document.getElementById("visible-script-code");
      if (existing) existing.remove();

      const el = document.createElement("pre");
      el.textContent = code;
      el.style.position = "fixed";
      el.style.top = "0";
      el.style.left = "0";
      el.style.padding = "10px";
      el.style.background = "white";
      el.style.color = "black";
      el.style.fontSize = "14px";
      el.style.zIndex = "99999";
      el.id = "visible-script-code";
      document.body.appendChild(el);
    }, code);

    await this.page.waitForTimeout(duration);
  }
}

// -------------------- 管理画面操作系 --------------------
export class Admin {
  constructor(private page: Page) {}

  async activateTheme(themeSlug: string) {
    await this.page.goto(`/wp-admin/themes.php`, { waitUntil: "networkidle" });
    const themeSelector = `.theme[data-slug="${themeSlug}"]`;
    const activateButton = this.page.locator(
      `${themeSelector} .theme-actions .activate`
    );
    const isActive = await this.page.locator(`${themeSelector}.active`).count();
    if (isActive === 0) {
      await activateButton.click();
      await this.page.waitForSelector(`${themeSelector}.active`, {
        timeout: 5000,
      });
    }
  }
}
