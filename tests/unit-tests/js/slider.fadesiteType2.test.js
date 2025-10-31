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
    changeDuration: 1,
    effect: "fade",
    homeType: "siteType2",
  };
  return jest.requireActual("../../../js/src/slider.js");
});

import { Integlight_FadeSlider2 } from "../../../js/src/slider.js";

describe("Integlight_FadeSlider2", () => {
  let instance;
  const settings = { changeDuration: 1 }; // 1s display, 0.5s transition

  beforeEach(() => {
    jest.useFakeTimers();

    // Set up a standard DOM for most tests
    document.body.innerHTML = `
            <div class="slider">
                <div class="slides">
                    <div class="slide"><img src="img1.jpg" /></div>
                    <div class="slide"><img src="img2.jpg" /></div>
                    <div class="slide"><img src="img3.jpg" /></div>
                </div>
            </div>
        `;
    // Create an instance for tests to use
    instance = new Integlight_FadeSlider2(settings);
  });

  afterEach(() => {
    jest.useRealTimers();
    jest.clearAllTimers();
    document.body.innerHTML = "";
  });

  describe("Initialization", () => {
    it("should add .fade-effect class on initialization", () => {
      expect(instance.slider.classList.contains("fade-effect")).toBe(true);
    });

    it("should duplicate images to have at least 3 if initial count is less", () => {
      // This test needs a specific DOM setup
      document.body.innerHTML = `
                <div class="slider">
                    <div class="slides">
                        <div class="slide"><img src="img1.jpg"></div>
                        <div class="slide"><img src="img2.jpg"></div>
                    </div>
                </div>
            `;
      const testInstance = new Integlight_FadeSlider2(settings);
      expect(testInstance.images.length).toBe(3);
      expect(testInstance.images).toEqual(["img1.jpg", "img2.jpg", "img1.jpg"]);
    });

    it("should create exactly 3 slide elements in the container", () => {
      const slideElements = instance.slidesContainer.querySelectorAll(".slide");
      expect(slideElements.length).toBe(3);
      ["slide-left", "slide-center", "slide-right"].forEach((cls) => {
        expect(
          instance.slidesContainer.querySelector(`.${cls}`)
        ).not.toBeNull();
      });
    });

    it("should display the initial 3 slides with opacity 1", () => {
      instance.visibleSlides.forEach((slide) => {
        expect(slide.style.opacity).toBe("1");
      });
    });
  });

  describe("Slide Transition", () => {
    it("should fade out all visible slides when showSlide() is called", () => {
      instance.showSlide();
      instance.visibleSlides.forEach((slide) => {
        expect(slide.style.opacity).toBe("0");
      });
    });

    it("should change image sources and fade in after the transition duration", () => {
      const initialImageSources = instance.visibleSlides.map((slide) =>
        slide.querySelector("img").getAttribute("src")
      );
      expect(initialImageSources).toEqual(["img1.jpg", "img2.jpg", "img3.jpg"]);

      instance.showSlide();

      // changingDuration is displayDuration / 2 = 1 / 2 = 0.5s. Timeout is 500ms.
      jest.advanceTimersByTime(500);

      const newImageSources = instance.visibleSlides.map((slide) =>
        slide.querySelector("img").getAttribute("src")
      );

      expect(instance.baseIndex).toBe(1);
      expect(newImageSources).toEqual(["img2.jpg", "img3.jpg", "img1.jpg"]);

      instance.visibleSlides.forEach((slide) => {
        expect(slide.style.opacity).toBe("1");
      });
    });

    it("should loop back to the start after reaching the end of the image list", () => {
      instance.baseIndex = instance.images.length - 1; // index 2

      instance.showSlide();
      jest.advanceTimersByTime(500);

      expect(instance.baseIndex).toBe(0);
      const newImageSources = instance.visibleSlides.map((slide) =>
        slide.querySelector("img").getAttribute("src")
      );
      expect(newImageSources).toEqual(["img1.jpg", "img2.jpg", "img3.jpg"]);
    });

    it("should be called repeatedly by setInterval", () => {
      // Arrange: コンストラクタで setInterval が呼ばれるため、showSlide をスパイする
      const showSlideSpy = jest.spyOn(instance, "showSlide");

      // Act & Assert: 1回目のインターバル
      // settings.changeDuration は 1 なので、1000ms 進める
      jest.advanceTimersByTime(1000);
      expect(showSlideSpy).toHaveBeenCalledTimes(1);
      // Act & Assert: 2回目のインターバル
      jest.advanceTimersByTime(1000);
      expect(showSlideSpy).toHaveBeenCalledTimes(2);
    });
  });
});
