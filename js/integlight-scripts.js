function slider_slide($) {

    var $slider = $('.slider');
    var $slides = $slider.find('.slides');
    var $slide = $slides.find('.slide');
    var slideCount = $slide.length;
    var currentIndex = 0;
    var changeDuration = sliderSettings.changeDuration;

    var changeDurationTime = changeDuration / 2;


    var slideWidth = $slide.width();

    $slider.addClass('slide-effect');


    // クローン要素を追加
    $slides.append($slide.first().clone());
    $slides.prepend($slide.last().clone());

    function showSlide(index, animate = true) {
        if (animate) {
            $slides.css('transition', 'transform ' + changeDurationTime / 2 + 's ease-in-out');
        } else {
            $slides.css('transition', 'none');
        }
        $slides.css('transform', 'translateX(' + (-index * slideWidth) + 'px)');

    }

    function nextSlide() {
        currentIndex++;
        showSlide(currentIndex);
        if (currentIndex === slideCount) {
            currentIndex = 0;
            setTimeout(function () {
                showSlide(currentIndex, false);
            }, changeDurationTime * 1000 / 2); // アニメーション終了後にクローン位置から本来の位置に瞬時に移動
        }
    }

    setInterval(nextSlide, changeDuration * 1000); // changeDuration 秒ごとに次のスライドを表示

    // 初期状態の調整
    currentIndex = 1;
    showSlide(currentIndex, false);

}




function slider_fade($) {

    var $slider = $('.slider');
    var $slides = $slider.find('.slides');
    var $slide = $slides.find('.slide');
    var slideCount = $slide.length;
    var fadeDuration = sliderSettings.fadeDuration;
    var changeDuration = sliderSettings.changeDuration;
    var currentIndex = 0;
    // $sliderにフェードクラスを追加
    $slider.addClass('fade');
    // フェード時間をCSSに適用
    $slide.css('transition', 'opacity ' + fadeDuration  + 's');

    function showSlide() {
        currentIndex++;
        if (currentIndex === slideCount) {
            currentIndex = 0;
        }
        $slide.removeClass('active');
        $slide.eq(currentIndex).addClass('active');
    }

    setInterval(showSlide, changeDuration * 1000); // changeDuration 秒ごとに次のスライドを表示
    showSlide();
}


jQuery(document).ready(function ($) {
    if (sliderSettings.effect === 'fade') {
        slider_fade($);
    } else if (sliderSettings.effect === 'slide') {
        slider_slide($);
    }
    return;
});









