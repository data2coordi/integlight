document.addEventListener('DOMContentLoaded', function () {
    // グローバルオブジェクト wp.i18n を利用して翻訳済みの文字列を取得
    var message = wp.i18n.__("Hello", "integlight");
    var message2 = wp.i18n.__("Hello World Block", "integlight");
    console.log(message);
    console.log(message2);
    console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@xxx@@@@@@@@@@@@@@@');
    // ページに表示する例
    var p = document.createElement('p');
    p.textContent = message;
    document.body.appendChild(p);
});
