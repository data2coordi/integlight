jQuery(document).ready(function ($) {
    var $slider = $('.slider');
    var $slides = $slider.find('.slides');
    var $slide = $slides.find('.slide');
    var slideCount = $slide.length;
    var currentIndex = 0;
    var changeDuration = 3; // スライドの切り替え時間（秒）
    var slideWidth = $slide.width();

    // クローン要素を追加
    $slides.append($slide.first().clone());
    $slides.prepend($slide.last().clone());

    function showSlide(index, animate = true) {
        if (animate) {
            $slides.css('transition', 'transform 0.5s ease-in-out');
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
            }, 500); // アニメーション終了後にクローン位置から本来の位置に瞬時に移動
        }
    }

    setInterval(nextSlide, changeDuration * 1000); // changeDuration 秒ごとに次のスライドを表示

    // 初期状態の調整
    currentIndex = 1;
    showSlide(currentIndex, false);
});









