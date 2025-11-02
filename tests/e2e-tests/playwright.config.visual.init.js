import { defineConfig, devices } from "@playwright/test";

// 環境によってURLや認証ファイルパスが変わるため、定数として定義
const BASE_URL = "https://t2.auroralab-design.com";
const authFile = "playwright/.auth/user.json";

const visualTestCnf = [
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

const visualInitProjects = visualTestCnf.flatMap((conf) => [
  {
    name: `setting_${conf.testid}`,
    testDir: "./tests",
    testMatch: [/visual\.setting\.spec\.js/],
    dependencies: ["setup"],
    use: {
      ...devices["Desktop Chrome"],
      storageState: authFile,
      keyValue: conf,
    },
  },
  {
    name: `visual_${conf.testid}`,
    testDir: "./tests",
    snapshotDir: "./tests/visual.init/", // 期待値（比較元）画像
    testMatch: [/visual\.spec\.js/],
    dependencies: [`setting_${conf.testid}`],
    use: {
      ...devices["Desktop Chrome"],
    },
    workers: 4,
  },
]);

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
    ...visualInitProjects,
  ],
});
