jQuery(document).ready(function ($) {
    var currentIndex = 0;
    var slides = $('.slide');
    var slideCount = slides.length;

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
    }, 3000);
});
