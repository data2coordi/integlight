document.addEventListener('DOMContentLoaded', function () {
    // ブロックエディター内では処理しない
    if (window.wp && wp.blocks) {
        console.log('Block editor detected, skipping frontend script.');
        return;
    }

    const tabContainers = document.querySelectorAll('.tabs');

    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('.tab');
        if (tabs.length === 0) return;

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

        function setActiveTab(index) {
            tabs.forEach((t, i) => {
                t.style.display = i === index ? 'block' : 'none';
            });
            currentIndex = index;
        }

        function nextTab() {
            const nextIndex = (currentIndex + 1) % tabs.length;
            setActiveTab(nextIndex);
        }

        function prevTab() {
            const prevIndex = (currentIndex - 1 + tabs.length) % tabs.length;
            setActiveTab(prevIndex);
        }

        function resetAutoSlide() {
            clearInterval(autoSlide);
            autoSlide = setInterval(nextTab, 5000); // 5秒ごとにスライド
        }

        nextButton.addEventListener('click', () => {
            nextTab();
            resetAutoSlide();
        });

        prevButton.addEventListener('click', () => {
            prevTab();
            resetAutoSlide();
        });

        setActiveTab(0);
        let autoSlide = setInterval(nextTab, 5000);
    });
});
