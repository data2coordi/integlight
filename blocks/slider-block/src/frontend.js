document.addEventListener('DOMContentLoaded', function () {
    // ブロックエディター内では処理しない
    if (window.wp && wp.blocks) {
        console.log('Block editor detected, skipping frontend script.');
        return;
    }

    const blockSliderContainers = document.querySelectorAll(
        '.wp-block-integlight-slider-block.blockSliders'
    );

    blockSliderContainers.forEach(container => {
        const blockSliders = container.querySelectorAll(':scope > *');
        if (blockSliders.length === 0) return;

        console.log('Found block sliders:', blockSliders);

        // スライドボタンを作成
        const prevButton = document.createElement('button');
        prevButton.className = 'slide-button prev';
        prevButton.textContent = '<';

        const nextButton = document.createElement('button');
        nextButton.className = 'slide-button next';
        nextButton.textContent = '>';

        container.appendChild(prevButton);
        container.appendChild(nextButton);

        let currentIndex = 0;

        function setActiveBlockSlider(index) {
            blockSliders.forEach((t, i) => {
                t.style.opacity = i === index ? '1' : '0';
                t.style.transition = 'opacity 0.5s ease-in-out';
            });
            currentIndex = index;
        }

        function nextBlockSlider() {
            const nextIndex = (currentIndex + 1) % blockSliders.length;
            setActiveBlockSlider(nextIndex);
        }

        function prevBlockSlider() {
            const prevIndex = (currentIndex - 1 + blockSliders.length) % blockSliders.length;
            setActiveBlockSlider(prevIndex);
        }

        function resetAutoSlide() {
            clearInterval(autoSlide);
            autoSlide = setInterval(nextBlockSlider, 5000); // 5秒ごとにスライド
        }

        nextButton.addEventListener('click', () => {
            nextBlockSlider();
            resetAutoSlide();
        });

        prevButton.addEventListener('click', () => {
            prevBlockSlider();
            resetAutoSlide();
        });

        setActiveBlockSlider(0);
        let autoSlide = setInterval(nextBlockSlider, 5000);
    });
});
