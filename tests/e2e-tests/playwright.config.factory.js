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

export function createVisualConfig({
  baseURL,
  testConfigs,
  pages,
  snapshotDir,
  projectPrefix = "",
}) {
  const visualProjects = testConfigs.flatMap((conf) => [
    {
      name: `setting_${projectPrefix}${conf.testid}`,
      testDir: "./tests",
      testMatch: [/visual\.setting\.spec\.js/],
      dependencies: ["setup"],
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
        // 'keyValue'よりも意図が明確な'testData'に変更
        testData: conf,
        pages,
      },
    },
    {
      name: `visual_${projectPrefix}${conf.testid}`,
      testDir: "./tests",
      outputDir: `test-results/visual_${projectPrefix}${conf.testid}`,
      // snapshotDirが指定されている場合のみ設定に追加
      ...(snapshotDir && { snapshotDir }),
      testMatch: [/visual\.spec\.js/],
      dependencies: [`setting_${projectPrefix}${conf.testid}`],
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
