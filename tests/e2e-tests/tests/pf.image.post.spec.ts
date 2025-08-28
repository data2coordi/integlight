import { test, type Page } from '@playwright/test';

type ScenarioConfig = {
    url: string;
    priorityCount: number; // 優先画像の数（キャッチ画像数）
    thumbnailImage: boolean; // thumbnailImageがあるか
};

const SCENARIOS: ScenarioConfig[] = [
    { url: 'https://wpdev.toshidayurika.com/portfolio-all-seasons-improved/', priorityCount: 1, thumbnailImage: true },
    { url: 'https://wpdev.toshidayurika.com/%e7%94%bb%e5%83%8f%e3%83%86%e3%82%b9%e3%83%88/', priorityCount: 3, thumbnailImage: false },
];

// SP / PC の環境定義
const DEVICES = {
    SP: { viewport: { width: 375, height: 800 }, userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1' },
    PC: { viewport: { width: 1280, height: 800 }, userAgent: undefined },
};

async function hasEasyLinkParent(img: Locator): Promise<boolean> {
    let current = img.locator('xpath=..');
    for (let i = 0; i < 5; i++) { // 最大5階層までチェック
        const classAttr = await current.getAttribute('class');
        if (classAttr?.includes('easyLink-box')) return true;
        current = current.locator('xpath=..');
    }
    return false;
}

// 画像属性チェック関数
async function verifyImageAttributes(page: Page, priorityCount: number, thumbnailImage: boolean) {
    const allImages = page.locator('.site-main article img');
    const images: any[] = [];

    const allCount = await allImages.count();
    for (let i = 0; i < allCount; i++) {
        const img = allImages.nth(i);

        // 親に .easyLink-box がある場合はスキップ
        if (await hasEasyLinkParent(img)) continue;

        images.push(img);
    }


    //console.log(`画像の総数: ${images.length} 優先画像数: ${priorityCount} fetchpriority検証：${thumbnailImage}`);

    for (let i = 0; i < images.length; i++) {
        const img = images[i]; // ← ここを修正
        const loading = await img.getAttribute('loading');
        const fetchpriority = await img.getAttribute('fetchpriority');

        const src = await img.getAttribute('src');
        //  console.log(`[${i + 1}枚目:] fetchpriority="${fetchpriority}" | loading="${loading}" | src:${src}`);

        if (thumbnailImage) {

            if (i < priorityCount) {
                if (loading !== 'eager') {
                    throw new Error(`[${i + 1}枚目] loading expected="eager", actual="${loading}"`);
                }
                if (fetchpriority !== 'high') {
                    throw new Error(`[${i + 1}枚目] fetchpriority expected="high", actual="${fetchpriority}"`);
                }
            } else {
                if (loading !== 'lazy') {
                    throw new Error(`[${i + 1}枚目] loading expected="lazy", actual="${loading}"`);
                }
                if (fetchpriority !== 'low') {
                    throw new Error(`[${i + 1}枚目] fetchpriority expected="low", actual="${fetchpriority}"`);
                }
            }
        } else {
            if (i < priorityCount) {
                if (loading !== null) {
                    throw new Error(`[${i + 1}枚目] loading expected="null", actual="${loading}"`);
                }
                if (fetchpriority !== null) {
                    throw new Error(`[${i + 1}枚目] fetchpriority expected="null", actual="${fetchpriority}"`);
                }
            } else {
                if (loading !== 'lazy') {
                    throw new Error(`[${i + 1}枚目] loading expected="lazy", actual="${loading}"`);
                }
                if (fetchpriority !== null) {
                    throw new Error(`[${i + 1}枚目] fetchpriority expected="null", actual="${fetchpriority}"`);
                }
            }

        }
    }
}

// 共通テスト関数
async function runTestForDevice(page: Page, scenario: ScenarioConfig) {
    await page.goto(scenario.url, { waitUntil: 'networkidle' });
    await verifyImageAttributes(page, scenario.priorityCount, scenario.thumbnailImage);
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
