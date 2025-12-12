import { test, expect, type Page } from "@playwright/test";
import { Admin } from "../utils/commonClass";

const POST_DATA = {
  title: "これは日本語のテスト投稿のタイトルです。AIスラッグを検証します。",
};

test.describe("Gemini AI スラッグ自動生成機能 E2Eテスト (URL直接検証)", () => {
  test("E2E: 新規投稿 - 日本語タイトルからAIスラッグが生成され、パーマリンクで検証", async ({
    page,
  }) => {
    console.log(
      "[12_aiSlug.spec.ts] ===== START: 日本語タイトルからAIスラッグが生成され、パーマリンクで検証 (最終版) ====="
    );

    // ---------------------------
    // 1. 新規投稿作成・公開
    // ---------------------------
    await page.goto("/wp-admin/post-new.php");
    const titleField = page.getByLabel("タイトルを追加");
    await titleField.fill(POST_DATA.title);

    // 公開処理 (安定版のクリックロジックを使用)
    await page
      .getByRole("button", { name: "公開", exact: true })
      .first()
      .click();
    const publishPanel = page.getByRole("region", {
      name: "エディターの投稿パネル",
    });
    await publishPanel
      .getByRole("button", { name: "公開", exact: true })
      .click();

    // 保存完了を待機 (AI処理完了までの時間も含む)
    await page.waitForSelector(".components-snackbar-list", {
      state: "attached",
      timeout: 30000,
    });

    // ---------------------------
    // 2. 投稿一覧画面へ移動し、パーマリンクを取得
    // ---------------------------

    console.log(
      "-> 投稿一覧画面へ移動し、生成されたパーマリンクを検証します。"
    );
    await page.goto("/wp-admin/edit.php", { waitUntil: "domcontentloaded" });

    // 投稿一覧のテーブル内で、作成した投稿（タイトルで特定）を見つける
    const postRow = page.getByRole("row", {
      name: new RegExp(POST_DATA.title),
    });

    // 3. パーマリンク（「表示」リンクの href 属性）を取得
    //    postRow のスコープ内で '表示' リンクを探す
    const viewLink = postRow.getByRole("link", { name: "表示" });

    // リンク先 URL を取得
    const permalink = await viewLink.getAttribute("href");

    expect(permalink).not.toBeNull();
    const url = new URL(permalink!);

    // URLのパスからスラッグ部分を抽出
    // 例: /post-type/kore-wa-nihongo-no.../
    const decodedPath = decodeURIComponent(url.pathname);
    const pathSegments = decodedPath.split("/").filter((s) => s.length > 0);
    const generatedSlug = pathSegments.pop(); // 配列の最後の要素がスラッグ

    if (!generatedSlug) {
      throw new Error(
        "URLからスラッグ（パスの最終セグメント）を抽出できませんでした。"
      );
    }

    // ---------------------------
    // 4. スラッグの形式要件を検証
    // ---------------------------

    console.log(`取得されたスラッグ: ${generatedSlug}`);

    // スラッグが半角小文字、数字、ハイフンのみで構成されていることを検証
    const slugRegex = /^[a-z0-9\-]+$/;
    expect(generatedSlug).toMatch(slugRegex);

    // (オプション: フロントエンドでの正常アクセス確認)
    await page.goto(permalink!);
    // 投稿タイトルがページ内にあることを確認することで、404エラーでないことを保証
    await expect(
      page.getByRole("heading", { name: POST_DATA.title, exact: true })
    ).toBeVisible();

    console.log(
      `✅ 検証完了: スラッグ (${generatedSlug}) はAI生成後の形式要件を満たし、フロントエンドで正常にアクセス可能です。`
    );

    // ---------------------------
    // 5. クリーンアップ (任意)
    // ---------------------------
    // 投稿IDを取得し、admin.deletePost(postId) を実行...

    console.log(
      "[12_aiSlug.spec.ts] ===== END: 日本語タイトルからAIスラッグが生成され、パーマリンクで検証 (最終版) ====="
    );
  });
});
