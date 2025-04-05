const { chromium } = require('playwright');

(async () => {
    const id = process.argv.slice(2);
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    // WordPress管理画面にログイン（必要に応じて）
    await page.goto('http://toshidayurika.com:7100/wp-admin');
    await page.fill('#user_login', id[0]);
    await page.fill('#user_pass', id[1]);
    await page.click('#wp-submit');

    // テーマカスタマイザーにアクセス
    await page.goto('http://toshidayurika.com:7100/wp-admin/customize.php');

    // ベースカラー設定を変更
    await page.click('text=Base color pattern');
    await page.click('text=Green');
    await page.click('text=Blue');

    // 設定を保存して公開
    await page.click('input[type="submit"][name="save"]');


    // フロントエンドページを確認
    await page.goto('http://toshidayurika.com:7100');
    const styleSheets = await page.$$eval('link[rel="stylesheet"]', links => links.map(link => link.href));
    const matchedStyleSheet = styleSheets.find(href => href.includes('/css/pattern2.css?ver=1.0.0'));
    
    if (!matchedStyleSheet) {
        console.error('CSS link does not contain the expected pattern2.css');
        process.exit(1);
    }

    console.log('Test passed: CSS link contains the expected pattern2.css');
    await browser.close();
})();
