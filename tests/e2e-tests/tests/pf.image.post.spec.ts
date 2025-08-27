import { test, type Page } from '@playwright/test';

type ScenarioConfig = {
    url: string;
    priorityCount: number; // 優先画像の数（キャッチ画像数）
    checkFetchpriority: boolean; // fetchpriorityをチェックするか
};

const SCENARIOS: ScenarioConfig[] = [
    { url: 'https://wpdev.toshidayurika.com/portfolio-all-seasons-improved/', priorityCount: 1, checkFetchpriority: true },
    { url: 'https://wpdev.toshidayurika.com/profile/', priorityCount: 3, checkFetchpriority: false },
];

// SP / PC の環境定義
const DEVICES = {
    SP: { viewport: { width: 375, height: 800 }, userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1' },
    PC: { viewport: { width: 1280, height: 800 }, userAgent: undefined },
};

// 画像属性チェック関数
async function verifyImageAttributes(page: Page, priorityCount: number, checkFetchpriority: boolean) {
    const images = page.locator('img');
    const count = await images.count();

    for (let i = 0; i < count; i++) {
        const img = images.nth(i);
        const loading = await img.getAttribute('loading');
        const fetchpriority = await img.getAttribute('fetchpriority');

        if (i < priorityCount) {
            if (loading !== null) throw new Error(`[${i + 1}枚目] loading should be unset, actual="${loading}"`);
            if (checkFetchpriority && fetchpriority !== null) throw new Error(`[${i + 1}枚目] fetchpriority should be unset, actual="${fetchpriority}"`);
        } else {
            if (loading !== 'lazy') throw new Error(`[${i + 1}枚目] loading expected="lazy", actual="${loading}"`);
            if (checkFetchpriority && fetchpriority !== 'low') throw new Error(`[${i + 1}枚目] fetchpriority expected="low", actual="${fetchpriority}"`);
        }
    }
}

// 共通テスト関数
async function runTestForDevice(page: Page, scenario: ScenarioConfig) {
    await page.goto(scenario.url, { waitUntil: 'networkidle' });
    await verifyImageAttributes(page, scenario.priorityCount, scenario.checkFetchpriority);
}

// デバイスごとにループ
for (const [deviceLabel, deviceConfig] of Object.entries(DEVICES)) {
    test.describe(`${deviceLabel}環境`, () => {
        for (const scenario of SCENARIOS) {
            test(`${scenario.url} 画像属性チェック`, async ({ browser }) => {
                const context = await browser.newContext({
                    viewport: deviceConfig.viewport,
                    userAgent: deviceConfig.userAgent,
                });
                const page = await context.newPage();
                await runTestForDevice(page, scenario);
                await page.close();
                await context.close();
            });
        }
    });
}
