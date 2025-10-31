import { defineConfig, devices } from "@playwright/test";

// 環境によってURLや認証ファイルパスが変わるため、定数として定義
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
];

const visualInitProjects = visualInitTestCnf.flatMap(
  ({
    testid,
    siteType,
    headerType,
    sliderType,
    //headerImageImg,
    //headerImageText,
    //sliderImg,
    //sliderText,
  }) => [
    {
      name: `setting_init_${testid}`,
      testDir: "./tests",
      testMatch: [/visual\.setting\.spec\.js/],
      dependencies: ["setup_init"],
      use: {
        baseURL: "https://t2.auroralab-design.com",
        ...devices["Desktop Chrome"],
        actionTimeout: 10_000,
        // 動画録画設定
        video: {
          mode: "on",
          retainOnFailure: true, // 成功・失敗に関わらず動画を残す
        },
        keyValue: {
          testid,
          siteType,
          headerType,
          sliderType,
          //  headerImageImg,
          //  headerImageText,
          //  sliderImg,
          //  sliderText,
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
        actionTimeout: 10_000,
        // 動画録画設定
        video: {
          mode: "on",
          retainOnFailure: true, // 成功・失敗に関わらず動画を残す
        },
      },
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

  // 複数のテストプロジェクトを定義
  ...visualInitProjects,
});
