import { defineconfig, devices } from "@playwright/test";

// 環境によってurlや認証ファイルパスが変わるため、定数として定義
const authfile = "playwright/.auth/user.json";

const visualinittestcnf = [
  // ... (visualinittestcnf の定義は省略せずそのまま)
  {
    testid: "elegant_slider",
    sitetype: "エレガント",
    headertype: "スライダー",
    slidertype: {
      effect: "スライド",
      interval: "60",
    },
    sliderimg: { imagepartialname: "firefly-1498.webp" },
    slidertext: {
      maintext: "visual init slider main テストタイトル",
      subtext: "visual init slider sub テストタイトル",
      top: "10",
      left: "10",
      devicetype: "pc",
      textcolor: "#0000ff",
      textfont: "yu_mincho",
    },
  },
];

const visualinitprojects = visualinittestcnf.flatmap(
  ({
    testid,
    sitetype,
    headertype,
    slidertype,
    //headerimageimg,
    //headerimagetext,
    //sliderimg,
    //slidertext,
  }) => [
    {
      name: `setting_init_${testid}`,
      testdir: "./tests",
      testmatch: [/visual\.setting\.spec\.js/],
      //dependencies: ["setup_init"],
      use: {
        baseurl: "https://t2.auroralab-design.com",
        ...devices["desktop chrome"],
        actiontimeout: 10_000,
        // 動画録画設定
        video: {
          mode: "on",
          retainonfailure: true, // 成功・失敗に関わらず動画を残す
        },
        //storagestate: authfile,
        keyvalue: {
          testid,
          sitetype,
          headertype,
          slidertype,
          //  headerimageimg,
          //  headerimagetext,
          //  sliderimg,
          //  slidertext,
        },
      },
    },
    {
      name: `visual_init_${testid}`,
      testdir: "./tests",
      snapshotdir: "./tests/visual.init/", // 期待値（比較元）画像
      testmatch: [/visual\.spec\.js/],
      dependencies: [`setting_init_${testid}`],
      use: {
        baseurl: "https://t2.auroralab-design.com",
        ...devices["desktop chrome"],
        actiontimeout: 10_000,
        // 動画録画設定
        video: {
          mode: "on",
          retainonfailure: true, // 成功・失敗に関わらず動画を残す
        },
      },
    },
  ]
);

export default defineconfig({
  // ... (reporterのコメントアウトはそのまま)
  // 各テストのデフォルトタイムアウト（ms）
  timeout: 60_000,

  // 複数のテストプロジェクトを定義
  projects: [
    // 👈 配列を開始
    ...visualinitprojects, // 👈 プロジェクトを展開
    {
      // 👈 setup_init プロジェクトを配列の要素として追加
      name: "setup_init",
      testmatch: "auth.setup.ts",
      use: {
        baseurl: "https://t2.auroralab-design.com",
        video: {
          mode: "on",
          retainonfailure: true, // 成功・失敗に関わらず動画を残す
        },
      },
    },
  ], // 👈 配列を閉じる
});
