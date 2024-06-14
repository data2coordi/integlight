jQuery(document).ready(function ($) {
    var currentIndex = 0;
    var slides = $('.slide');
    var slider = document.querySelector('.slider');
    var slideCount = slides.length;
    var fadeDuration = sliderSettings.fadeDuration;
    var changeDuration = sliderSettings.changeDuration;

    //slider.classList.add('fade');
    slider.classList.add('slide-effect');

    // フェード時間をCSSに適用
    slides.css('transition', 'opacity ' + fadeDuration + 's');
    /*

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === index) {
                slide.classList.add('active');
            }
        });
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
    }

    setInterval(nextSlide, 3000); // 3秒ごとに次のスライドを表示

    showSlide(currentIndex);
    */







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
