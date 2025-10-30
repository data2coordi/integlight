/**
 * @jest-environment jsdom
 */

// slider.js のインポート時に`integlight_sliderSettings`が必要なため、モックで先に定義します。
jest.mock("../../../js/src/slider.js", () => {
  global.integlight_sliderSettings = {
    fadeName: "fade",
    slideName: "slide",
    siteType1Name: "siteType1",
    siteType2Name: "siteType2",
    changeDuration: 2.5,
    effect: "slide",
    homeType: "siteType2",
  };
  return jest.requireActual("../../../js/src/slider.js");
});

import { Integlight_SlideSlider2 } from "../../../js/src/slider.js";

describe("Integlight_SlideSlider2 (Plain JS)", () => {
  let instance;
  let slidesContainer;
  const settings = { changeDuration: 2.5 };

  // ヘルパー関数でDOMセットアップとスパイ設定を共通化
  const setupSliderDOM = (slidesHTML) => {
    document.body.innerHTML = `
            <div class="slider">
                <div class="slides">
                    ${slidesHTML}
                </div>
            </div>
        `;
    document.querySelectorAll(".slide").forEach((slide) => {
      Object.defineProperty(slide, "offsetWidth", {
        configurable: true,
        value: 100,
      });
    });
    slidesContainer = document.querySelector(".slides");
    // transitionend イベントを手動で発火させるためのスパイ
    jest
      .spyOn(slidesContainer, "addEventListener")
      .mockImplementation((event, cb, options) => {
        if (event === "transitionend" && options?.once) {
          setTimeout(() => cb(), 0); // 0ms後にコールバックを実行
        }
      });
  };

  beforeEach(() => {
    jest.useFakeTimers();
    setupSliderDOM(`
            <div class="slide">Slide 1</div>
            <div class="slide">Slide 2</div>
            <div class="slide">Slide 3</div>
        `);
    instance = new Integlight_SlideSlider2(settings);
  });

  afterEach(() => {
    jest.useRealTimers();
    jest.clearAllMocks();
    document.body.innerHTML = ""; // DOMをクリーンアップ
  });

  it("初期化時に slide-effect クラスが付与される", () => {
    expect(
      document.querySelector(".slider").classList.contains("slide-effect")
    ).toBe(true);
    expect(instance.currentIndex).toBe(2);

    // 3 (オリジナル) + 4 (クローン) = 7
    expect(slidesContainer.children.length).toBe(7);
    expect(slidesContainer.children[0].textContent).toBe("Slide 2"); // prependされたクローン
    expect(slidesContainer.children[6].textContent).toBe("Slide 2"); // appendされたクローン
  });

  it("slideWidth が正しく取得される", () => {
    expect(instance.slideWidth).toBe(100);
  });

  it("showSlide で transform が更新される", () => {
    jest.advanceTimersByTime(instance.displayDuration * 1000);

    expect(instance.currentIndex).toBe(3);
    expect(slidesContainer.style.transition).toContain("ease-out");
    // Assert that the transform value is calculated correctly based on the instance's state
    const expectedX =
      -instance.currentIndex * instance.slideWidth + instance.offset;
    expect(slidesContainer.style.transform).toBe(`translateX(${expectedX}px)`);
  });

  it("最後のスライドに到達後にループして最初のスライドに戻る", () => {
    instance.currentIndex = instance.slideCount + 1; // 4

    instance.showSlide(); // currentIndex++ → 5, transitionend 登録

    // イベントを発火非常に小さい値にすることでtransitionendイベントのみ発火させる。timerによる次のshowSlideは実行させない。
    jest.advanceTimersByTime(instance.displayDuration * 1);

    expect(instance.currentIndex).toBe(2);

    const expectedX =
      -instance.currentIndex * instance.slideWidth + instance.offset;
    expect(instance.slidesContainer.style.transform).toBe(
      `translateX(${expectedX}px)`
    );
    expect(instance.slidesContainer.style.transition).toBe("none");
  });

  it("自動で showSlide が繰り返し呼ばれる", () => {
    const spy = jest.spyOn(instance, "showSlide");

    // 3回分のインターバルを実行 (2.5s * 3 = 7.5s)
    jest.advanceTimersByTime(settings.changeDuration * 1000 * 3);
    expect(spy).toHaveBeenCalledTimes(3);

    spy.mockRestore();
  });

  it("スライド2枚でも初期化と動作が正常", () => {
    // Arrange: ヘルパー関数で2枚スライドのDOMをセットアップ
    setupSliderDOM(`
                <div class="slide">Slide 1</div>
                <div class="slide">Slide 2</div>
            `);

    // Act
    const twoSlideInstance = new Integlight_SlideSlider2(settings);

    // Assert: 初期化
    expect(twoSlideInstance.slideCount).toBe(2);
    expect(document.querySelectorAll(".slides .slide").length).toBe(6); // 2 original + 4 clones
    expect(twoSlideInstance.currentIndex).toBe(2);

    // Act & Assert: スライド実行
    twoSlideInstance.showSlide();
    expect(twoSlideInstance.currentIndex).toBe(3);
  });
});
