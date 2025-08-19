// utils/customizer.ts
import { expect, type Page } from '@playwright/test';

// -------------------- 計測系 --------------------
const stepTimers = new Map<string, number>();

export function timeStart(stepName: string) {
    stepTimers.set(stepName, Date.now());
}

export function logStepTime(stepName: string) {
    const startTime = stepTimers.get(stepName);
    if (startTime) {
        const duration = Date.now() - startTime;
        console.log(`[Timer] Step "${stepName}" took ${duration}ms`);
    }
}

// -------------------- カスタマイザー操作系 --------------------
export async function openCustomizer(page: Page) {
    await page.goto(`/wp-admin/customize.php?url=${encodeURIComponent('/')}`, {
        waitUntil: 'networkidle',
    });
    await expect(page.locator('.wp-full-overlay-main')).toBeVisible();
}

export async function openHeaderSetting(page: Page, setting: string) {
    await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
    await page.getByRole('button', { name: 'スライダーまたは画像を選択' }).click();
    const effectSelect = page.getByRole('combobox', { name: 'スライダーまたは画像を表示' });
    await effectSelect.selectOption({ label: setting });
}

export async function selSliderFad(page: Page) {
    await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
    await page.getByRole('button', { name: 'スライダー設定' }).click();
    const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
    await effectSelect.selectOption({ label: 'フェード' });
}

export async function saveCustomizer(page: Page) {
    const saveBtn = page.locator('#save');
    if (!(await saveBtn.isEnabled())) {
        return;
    }
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();
}

export async function setSiteType(page: Page, siteType: string) {
    await page.getByRole('button', { name: 'サイトタイプ設定' }).click();
    const checkbox = page.getByLabel(siteType);
    if (!(await checkbox.isChecked())) {
        await checkbox.check();
    }
}

export async function ensureCustomizerRoot(page: Page) {
    await page.evaluate(() => {
        if (window.wp && window.wp.customize) {
            try {
                window.wp.customize.panel.each(panel => {
                    if (typeof panel.collapse === 'function') panel.collapse();
                });
                window.wp.customize.section.each(section => {
                    if (typeof section.collapse === 'function') section.collapse();
                });
            } catch (e) {
                // fallback
            }
        }
    });
    await page.waitForTimeout(200);
}
