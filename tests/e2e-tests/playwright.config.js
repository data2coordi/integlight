import { defineConfig, devices } from "@playwright/test";

// 環境によってURLや認証ファイルパスが変わるため、定数として定義
const BASE_URL = "https://wpdev.auroralab-design.com";
const authFile = "playwright/.auth/user.json";

const visualInitTestCnf = [
  {
    testid: "elegant_slider",
    siteType: "エレガント",
    headerType: "スライダー",
    sliderType: {
      effect: "スライド",
      interval: "60",
    },
    sliderImg: { imagePartialName: "Firefly-1498.webp" },
    sliderText: {
      mainText: "visual init slider main テストタイトル",
      subText: "visual init slider sub テストタイトル",
      top: "10",
      left: "10",
      deviceType: "PC",
      textColor: "#0000ff",
      textFont: "yu_mincho",
    },
  },
  {
    testid: "pop_slider",
    siteType: "ポップ",
    headerType: "スライダー",
    sliderType: { effect: "スライド", interval: "60" },
  },
  {
    testid: "pop_img",
    siteType: "ポップ",
    headerType: "静止画像",
    headerImageImg: { imageName: "Firefly-1498.webp" },
    headerImageText: {
      mainText: "テストタイトル",
      subText: "これはPlaywrightテストによって入力された説明文です。",
      textFont: "yu_mincho",
      textPositionTop: "20",
      textPositionLeft: "30",
      textPositionTop_mobile: "5",
      textPositionLeft_mobile: "10",
      textColor: "#ff0000",
    },
  },
];

const visualInitProjects = visualInitTestCnf.flatMap(
  ({
    testid,
    siteType,
    headerType,
    sliderType,
    headerImageImg,
    headerImageText,
    sliderImg,
    sliderText,
  }) => [
    {
      name: `setting_init_${testid}`,
      testDir: "./tests",
      testMatch: [/visual\.setting\.spec\.js/],
      dependencies: ["setup_init"],
      use: {
        baseURL: "https://t2.auroralab-design.com",
        storageState: authFile,
        keyValue: {
          testid,
          siteType,
          headerType,
          sliderType,
          headerImageImg,
          headerImageText,
          sliderImg,
          sliderText,
        },
      },
    },
    {
      name: `visual_init_${testid}`,
      testDir: "./tests",
      snapshotDir: "./tests/visual.init/", // 期待値（比較元）画像
      testMatch: [/visual\.spec\.js/],
      dependencies: [`setting_init_${testid}`],
      use: {
        baseURL: "https://t2.auroralab-design.com",
        ...devices["Desktop Chrome"],
      },
      workers: 4,
    },
  ]
);

const visualTestCnf = [
  {
    testid: "elegant_slider",
    siteType: "エレガント",
    headerType: "スライダー",
    sliderType: { effect: "スライド", interval: "60" },
  },
  {
    testid: "pop_slider",
    siteType: "ポップ",
    headerType: "スライダー",
    sliderType: { effect: "スライド", interval: "60" },
  },
  {
    testid: "pop_img",
    siteType: "ポップ",
    headerType: "静止画像",
  },
];

const visualProjects = visualTestCnf.flatMap(
  ({ testid, siteType, headerType, sliderType }) => [
    {
      name: `setting_${testid}`,
      testDir: "./tests",
      testMatch: [/visual\.setting\.spec\.js/],
      dependencies: ["setup"],
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
        keyValue: {
          testid,
          siteType,
          headerType,
          sliderType,
        },
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
        keyValue: {
          testid,
          siteType,
          headerType,
        },
      },
      workers: 4,
    },
  ]
);

export default defineConfig({
  // reporter: [
  //   ["list"],
  //   [
  //     "html",
  //     {
  //       open: "never",
  //       outputFolder: "test-results",
  //     },
  //   ],
  // ],
  // 各テストのデフォルトタイムアウト（ms）
  timeout: 60_000,

  // プロジェクト間で共有される設定
  use: {
    // click や fill など1アクションのタイムアウト
    actionTimeout: 10_000,
    // 動画録画設定
    video: {
      mode: "on",
      retainOnFailure: true, // 成功・失敗に関わらず動画を残す
      // videosPath を指定して、動画を別の専用フォルダに確実に保存
      // ただし、通常このオプションは不要で、単に retainOnFailure: true で十分なはずです
      // videosPath: './test-results/videos',
    },

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
    {
      name: "setup_init",
      testMatch: "auth.setup.ts",
      use: {
        baseURL: "https://t2.auroralab-design.com", // ← ここに入れる！
      },
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
        /visual\.setting\.spec\.js/,
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
    ...visualInitProjects,
  ],
});
