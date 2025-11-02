import { createVisualConfig } from "./playwright.config.factory.js";

const BASE_URL = "https://t2.auroralab-design.com";

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

export default createVisualConfig({
  baseURL: BASE_URL,
  testConfigs: visualTestCnf,
  snapshotDir: "./tests/visual.init/", // 期待値（比較元）画像
  projectPrefix: "init_iii_", // プロジェクト名を区別するためのプレフィックス
});
