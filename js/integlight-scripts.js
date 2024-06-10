jQuery(document).ready(function ($) {
    var currentIndex = 0;
    var slides = $('.slide');
    var slideCount = slides.length;


    console.log("init");
    function showNextSlide() {
        console.log("next slide");
        slides.eq(currentIndex).removeClass('active');
        currentIndex = (currentIndex + 1) % slideCount;
        slides.eq(currentIndex).addClass('active');
    }

    // 初期表示
    slides.eq(currentIndex).addClass('active');

    // 5秒ごとにスライドを切り替える
    setInterval(showNextSlide, 3000);
});