import { createVisualConfig } from "./playwright.config.factory.js";

const BASE_URL = "https://wpdev.auroralab-design.com";

const visualTestCnf = [
  {
    testid: "elegant_slider",
    customizer: {
      siteType: "エレガント",
      headerType: "スライダー",
      sliderType: { effect: "スライド", interval: "60" },
    },
  },
  {
    testid: "pop_slider",
    customizer: {
      siteType: "ポップ",
      headerType: "スライダー",
      sliderType: { effect: "スライド", interval: "60" },
    },
  },
  {
    testid: "pop_img",
    customizer: {
      siteType: "ポップ",
      headerType: "静止画像",
    },
  },
];

const pages = [
  { name: "home top", url: "/" },
  {
    name: "front top",
    url: "/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/",
  },
  { name: "カテゴリ一覧", url: "/category/fire-blog/" },
  { name: "固定ページ", url: "/profile/" },
  { name: "ブログ", url: "/sidefire-7500man-life-cost/" },
  { name: "プラグイン1", url: "/ptest/" },
  { name: "プラグイン2", url: "/ptest2/" },
];

export default createVisualConfig({
  baseURL: BASE_URL,
  testConfigs: visualTestCnf,
  pages: pages,
});
