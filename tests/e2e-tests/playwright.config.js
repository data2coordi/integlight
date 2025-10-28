import { defineConfig, devices } from "@playwright/test";

// 環境によってURLや認証ファイルパスが変わるため、定数として定義a
const BASE_URL = "https://wpdev.auroralab-design.com";
const authFile = "playwright/.auth/user.json";

const visualTestCnf = [
  {
    testid: "elegant_slider",
    siteType: "エレガント",
    headerType: "スライダー",
  },
  {
    testid: "pop_slider",
    siteType: "ポップ",
    headerType: "スライダー",
  },
  {
    testid: "elegant_img",
    siteType: "ポップ",
    headerType: "静止画像",
  },
];

const visualProjects = visualTestCnf.flatMap(
  ({ testid, siteType, headerType }) => [
    {
      name: `setting_${testid}`,
      testDir: "./tests",
      testMatch: [/setting\.spec\.js/],
      dependencies: ["setup"],
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
        siteType,
        headerType,
      },
    },
    {
      name: `visual_${testid}`,
      testDir: "./tests",
      testMatch: [/visual\.spec\.js/],
      dependencies: [`setting_${testid}`],
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
        siteType,
        headerType,
      },
    },
  ]
);

export default defineConfig({
  reporter: [
    ["list"],
    [
      "html",
      {
        open: "never",
        outputFolder: "test-results",
      },
    ],
  ],
  // 各テストのデフォルトタイムアウト（ms）
  timeout: 60_000,

  // プロジェクト間で共有される設定
  use: {
    // click や fill など1アクションのタイムアウト
    actionTimeout: 10_000,
    // 動画録画設定
    video: "off",
    // ブラウザのベースURL
    baseURL: BASE_URL,
  },

  // 複数のテストプロジェクトを定義
  projects: [
    // 1. 認証処理を実行するプロジェクト
    {
      name: "setup",
      testMatch: "auth.setup.ts",
    },

    // 2. 【新規】認証が不要なテスト用のプロジェクト
    {
      name: "unauthenticated",
      testMatch: [
        /menu\.spec\.js/,
        /htmlCode\.spec\.ts/,
        /pf\.image\.post\.spec\.ts/,
      ], // 認証不要なテストファイルを指定
      use: {
        ...devices["Desktop Chrome"],
        // storageState を使わないので、ログイン状態にはならない
      },
    },

    // 3. 【変更】認証が必要な本テストを実行するプロジェクト
    {
      name: "main",
      testDir: "./tests", // テストファイルのディレクトリを指定
      testIgnore: [
        /auth\.setup\.ts/,
        /menu\.spec\.js/,
        /htmlCode\.spec\.ts/,
        /pf\.image\.post\.spec\.ts/,
        /visual\.spec\.js/,
        /visual\.init\.spec\.js/,
      ], // setupと認証不要テストを除外
      dependencies: ["setup"], // setupプロジェクトの完了を待機
      use: {
        ...devices["Desktop Chrome"], // デスクトップChromeを使用
        // 保存した認証状態をロード
        storageState: authFile,
      },
      workers: 1,
    },
    // {
    //   name: "setting",
    //   testDir: "./tests", // テストファイルのディレクトリを指定
    //   testMatch: [/setting\.spec\.js/],
    //   dependencies: ["setup"], // setupプロジェクトの完了を待機
    //   use: {
    //     ...devices["Desktop Chrome"], // デスクトップChromeを使用
    //     // 保存した認証状態をロード
    //     storageState: authFile,
    //     siteType: "エレガント",
    //   },
    // },
    // {
    //   name: "visual",
    //   testDir: "./tests", // テストファイルのディレクトリを指定
    //   testMatch: [/visual\.spec\.js/],
    //   dependencies: ["setting"], // setupプロジェクトの完了を待機
    //   use: {
    //     ...devices["Desktop Chrome"], // デスクトップChromeを使用
    //     // 保存した認証状態をロード
    //     storageState: authFile,
    //   },
    // },
    {
      name: "visual.init",
      testDir: "./tests", // テストファイルのディレクトリを指定
      testMatch: [/visual\.init\.spec\.js/],
      dependencies: ["setup"], // setupプロジェクトの完了を待機
      use: {
        ...devices["Desktop Chrome"], // デスクトップChromeを使用
        // 保存した認証状態をロード
        storageState: authFile,
      },
      workers: 1,
    },

    ...visualProjects,
  ],
});
