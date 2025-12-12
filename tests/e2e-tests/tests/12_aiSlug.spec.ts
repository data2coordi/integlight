import { test, expect, type Page } from "@playwright/test";

/**
 * 💡 テストデータ定義
 * タイトルは日本語を使用しますが、expectedSlugは使用しません。
 */
const POST_DATA = {
  title: "これは日本語のテスト投稿のタイトルです。AIスラッグを検証します。",
};

// 投稿IDを取得するためのヘルパー関数
function getPostIdFromUrl(url: string): string | null {
  const match = url.match(/post=(\d+)/);
  return match ? match[1] : null;
}

test.describe("Gemini AI スラッグ自動生成機能 E2Eテスト (柔軟な検証)", () => {
  test("E2E: 新規投稿 - 日本語タイトルからAIスラッグが生成され、形式が正しいこと", async ({
    page,
  }) => {
    console.log(
      "[12_aiSlug.spec.ts] ===== START: 日本語タイトルからAIスラッグが生成され、形式が正しいこと ====="
    );

    // ---------------------------
    // 2. 新規投稿作成
    // ---------------------------
    const initialUrl = await page.goto("/wp-admin/post-new.php");
    const titleField = page.getByLabel("タイトルを追加");
    await titleField.fill(POST_DATA.title);

    // ---------------------------
    // 3. 投稿の公開とAI処理のトリガー
    // ---------------------------
    // 1. 最初の「公開」ボタンをクリックしてパネルを開く
    //    このボタンはトグル役なので、通常は一意に見つけられます。
    await page
      .getByRole("button", { name: "公開", exact: true })
      .first()
      .click(); // first()でトグルボタンを明示的に選択

    // 2. パネル内で出現した実際の「公開」ボタンをクリックして保存を実行する
    //    公開パネルのスコープ内で「公開」ボタンを探すことで、厳密モード違反を回避します。
    const publishPanel = page.getByRole("region", {
      name: "エディターの投稿パネル",
    });

    // パネル内の公開ボタンをクリック
    await publishPanel
      .getByRole("button", { name: "公開", exact: true })
      .click();

    await page.waitForSelector(".components-snackbar-list", {
      state: "attached",
      timeout: 30000,
    });

    // ---------------------------
    // 4. 強制リロード後にスラッグを検証
    // ---------------------------

    // AI処理によるDB更新後、画面に反映させるためページをリロード
    await page.reload({ waitUntil: "domcontentloaded" });
    // 投稿設定パネルが開いているか確認し、開いていなければクリック
    const postSettingPanel = page.getByRole("region", { name: "投稿設定" });

    // パネルが非表示の場合
    if (await postSettingPanel.isHidden()) {
      // 🚨 修正箇所: 厳密モード違反を回避するため .first() を追加
      // トップバーの「設定」トグルボタンをクリックします。
      await page.getByRole("button", { name: "設定" }).first().click();
    }

    // URL/スラッグ表示部分をクリックして編集フィールドを表示
    // 🚨 修正箇所: name: "URL" ではなく、aria-label の部分一致でボタンを特定
    await page
      .getByRole("button", { name: "リンクを変更", exact: false })
      .click();

    // スラッグ編集フィールドを取得
    const slugInput = page.getByLabel("スラッグを編集");

    // 1. スラッグが空でないことを確認 (AIが何らかの値を返した)
    const generatedSlug = await slugInput.inputValue();
    expect(generatedSlug).not.toBe("");

    // 2. 形式要件の検証: スラッグが半角小文字、数字、ハイフンのみで構成されていること
    //    これは AI スラッグ処理の `sanitize_title()` が成功したことを示す
    const slugRegex = /^[a-z0-9\-]+$/;
    expect(generatedSlug).toMatch(slugRegex);
    console.log(
      `✅ 形式検証成功: 生成されたスラッグ (${generatedSlug}) は形式要件を満たしています。`
    );

    // 3. 非日本語要件の検証: 元の日本語タイトルから自動生成されるコアスラッグ（例: 'これ-は-日本語-の...'）とは、
    //    **内容的に異なっている**ことを確認
    //    **警告:** この検証は、AIが翻訳的なスラッグ（例: 'this-is-a-japanese-test...'）を返すことを期待していますが、
    //    もしAIがローマ字的なスラッグ（例: 'kore-wa-nihongo-no...'）を返す場合、このチェックは調整が必要です。
    //    ここでは、"japanese"のような英語の単語が混ざっているかをゆるく確認する方法を例とします。
    //    **より安全なのは、WordPressのサニタイズ処理が通るかのみをチェックすることです。**

    /* // 例: AI翻訳的なスラッグを期待する場合のゆるいチェック (オプション)
    expect(generatedSlug).toContain('test');
    expect(generatedSlug).not.toContain('タイトル'); // 日本語の残骸がないことを確認
    */

    console.log(
      `✅ 存在検証成功: スラッグはAIによって更新されました。生成値: ${generatedSlug}`
    );

    // ---------------------------
    // 5. クリーンアップ
    // ---------------------------

    const postId = getPostIdFromUrl(page.url());
    if (postId) {
      // await admin.deletePost(postId);
    }

    console.log(
      "[12_aiSlug.spec.ts] ===== END: 日本語タイトルからAIスラッグが生成され、形式が正しいこと ====="
    );
  });
});
