jQuery(document).ready(function ($) {
    var currentIndex = 0;
    var slides = $('.slide');
    var slideCount = 3;
    ///var slideCount = slides.length;
    var fadeDuration = sliderSettings.fadeDuration;
    var changeDuration = sliderSettings.changeDuration;

    // フェード時間をCSSに適用
    slides.css('transition', 'opacity ' + fadeDuration + 's');


    function showNextSlide() {
        slides.eq(currentIndex).removeClass('active');
        currentIndex = (currentIndex + 1) % slideCount;
        slides.eq(currentIndex).addClass('active');
    }

    // 初期表示
    slides.eq(currentIndex).addClass('active');

    // 5秒ごとにスライドを切り替える
    setInterval(function () {
        showNextSlide();
    }, changeDuration * 1000);
});
