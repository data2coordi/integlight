import { createVisualConfig } from "./playwright.config.factory.js";

const BASE_URL = "https://wpdev.auroralab-design.com";

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

export default createVisualConfig({
  baseURL: BASE_URL,
  testConfigs: visualTestCnf,
});
