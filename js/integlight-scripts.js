
















jQuery(document).ready(function ($) {
    var currentIndex = 0;
    var $slides = $('.slider .slide');
    var $slider = $('.slider');
    var slideCount = $slides.length;
    var changeDuration = sliderSettings.changeDuration;
    var fadeDuration = sliderSettings.fadeDuration;

    // $sliderにフェードクラスを追加
    //$slider.addClass('fade');
    // フェード時間をCSSに適用
    //$slides.css('transition', 'opacity ' + fadeDuration + 's');

    $slider.addClass('slide-effect');

    function showSlide(index) {
        $slides.removeClass('active previous next');

        if (slideCount > 1) {

            // 前のスライドのインデックスを計算
            var prevIndex = (index - 1 + slideCount) % slideCount;
            var nextIndex = (index + 1) % slideCount;
            //console.log('start***************');
            //console.log('prev:' + prevIndex);
            //console.log('current:' + index);
            //console.log('next:' + nextIndex);
            //console.log('end***************');

            // 次のスライドのインデックスを計算
            $slides.eq(nextIndex).addClass('next');
            $slides.eq(prevIndex).addClass('previous');
            $slides.eq(index).addClass('active');

        } else {
            $slides.eq(index).addClass('active');
        }
    }

    function nextSlide() {
        currentIndex = (currentIndex + 1) % slideCount;
        showSlide(currentIndex);
    }

    setInterval(nextSlide, changeDuration * 1000); // 3秒ごとに次のスライドを表示

    //showSlide(currentIndex);
});


/*
jQuery(document).ready(function ($) {
    var currentIndex = 0;
    var slides = document.querySelectorAll('.slider .slide');
    var slider = document.querySelector('.slider');
    var slideCount = slides.length;
    var changeDuration = sliderSettings.changeDuration;

    //slider.classList.add('slide-effect');
    slider.classList.add('fade');

    var fadeDuration = sliderSettings.fadeDuration;
    var slidesj = $('.slide');
    // フェード時間をCSSに適用
    slidesj.css('transition', 'opacity ' + fadeDuration + 's');

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

    setInterval(nextSlide, changeDuration * 1000); // 3秒ごとに次のスライドを表示











    showSlide(currentIndex);

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

    */