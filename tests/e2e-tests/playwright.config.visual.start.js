import { createVisualConfig } from "./playwright.config.factory.js";

const BASE_URL = "https://t2.auroralab-design.com";

const visualTestCnf = [
  {
    testid: "start_a",
  },
];

const pages = [{ name: "home top", url: "/" }];

export default createVisualConfig({
  baseURL: BASE_URL,
  testConfigs: visualTestCnf,
  pages: pages,
  snapshotDir: "./tests/visual.start/", // 期待値（比較元）画像
  projectPrefix: "start_", // プロジェクト名を区別するためのプレフィックス
});
