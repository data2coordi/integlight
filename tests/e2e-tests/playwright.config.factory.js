import { defineConfig, devices } from "@playwright/test";

const authFile = "playwright/.auth/user.json";

/**
 * Visualテスト用のPlaywright設定を生成するファクトリ関数
 * @param {object} options
 * @param {string} options.baseURL - テスト対象のベースURL
 * @param {Array<object>} options.testConfigs - テストケースごとの設定配列 (visualTestCnf)
 * @param {string} [options.snapshotDir] - スナップショットを保存するディレクトリ (init用)
 * @param {string} [options.projectPrefix=''] - プロジェクト名に付与するプレフィックス (init用)
 * @returns {import('@playwright/test').PlaywrightTestConfig}
 */

const pages = [
  { name: "home top", url: "/" },
  {
    name: "front top",
    url: "/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/",
  },
  { name: "カテゴリ一覧", url: "/category/fire-blog/" },
  { name: "固定ページ", url: "/profile/" },
];

export function createVisualConfig({
  baseURL,
  testConfigs,
  snapshotDir,
  projectPrefix = "",
}) {
  const visualProjects = testConfigs.flatMap((conf) => [
    {
      name: `${projectPrefix}setting_${conf.testid}`,
      testDir: "./tests",
      testMatch: [/visual\.setting\.spec\.js/],
      dependencies: ["setup"],
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
        // 'keyValue'よりも意図が明確な'testData'に変更
        testData: conf,
      },
    },
    {
      name: `${projectPrefix}visual_${conf.testid}`,
      testDir: "./tests",
      outputDir: `test-results/${projectPrefix}visual_${conf.testid}`,
      // snapshotDirが指定されている場合のみ設定に追加
      ...(snapshotDir && { snapshotDir }),
      testMatch: [/visual\.spec\.js/],
      dependencies: [`${projectPrefix}setting_${conf.testid}`],
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
        pages,
      },
      workers: 4,
    },
  ]);

  return defineConfig({
    reporter: [
      ["list"],
      // [
      //   "html",
      //   {
      //     open: "never",
      //     outputFolder: "test-results",
      //   },
      // ],
    ],
    timeout: 60_000,
    use: {
      actionTimeout: 10_000,
      video: "off",
      baseURL: baseURL,
    },
    projects: [
      {
        name: "setup",
        testMatch: "auth.setup.ts",
        use: {
          baseURL: baseURL,
        },
      },
      ...visualProjects,
    ],
  });
}
