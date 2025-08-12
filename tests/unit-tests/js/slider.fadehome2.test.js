/**
 * @jest-environment jsdom
 */

beforeAll(() => {
    global.integlight_sliderSettings = {
        fadeName: 'fade',
        slideName: 'slide',
        home1Name: 'home1',
        home2Name: 'home2',
        changeDuration: 5,
        effect: 'fade',
        homeType: 'home2',
    };

    // 最低限の jQuery グローバル（副作用回避）
    global.jQuery = jest.fn(() => ({
        on: jest.fn(),
    }));
});

let Integlight_FadeSlider2;
let mock$,
    mockSliderElement,
    mockSlidesWrapper,
    mockSlideElements,
    slideMocks;

/**
 * slideMocks を使った map のモックを外に出して分かりやすく
 */
function createMockSlideElementsMap(cb) {
    const results = [];
    for (let i = 0; i < slideMocks.length; i++) {
        results.push(cb(i, slideMocks[i]));
    }
    return {
        get: () => results,
    };
}

beforeEach(async () => {
    jest.useFakeTimers();

    // 動的import（非同期）だがbeforeEachなので問題なし
    const module = await import('../../../js/src/slider.js');
    Integlight_FadeSlider2 = module.Integlight_FadeSlider2;

    // slideMocks 初期化
    slideMocks = [
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img1.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img2.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img3.jpg') })) },
    ];

    // mockSliderElement の定義
    mockSliderElement = {
        find: jest.fn((selector) => {
            if (selector === '.slides') return mockSlidesWrapper;
            return null;
        }),
        addClass: jest.fn(),
    };

    // mockSlidesWrapper の定義
    mockSlidesWrapper = {
        find: jest.fn((selector) => {
            if (selector === '.slide') return mockSlideElements;
            return null;
        }),
        empty: jest.fn(),
        append: jest.fn(),
    };

    // mockSlideElements の定義
    mockSlideElements = {
        length: slideMocks.length,
        width: jest.fn(() => 100),
        first: jest.fn(() => ({
            clone: jest.fn(() => 'cloned-first'),
        })),
        css: jest.fn(),
        append: jest.fn(),
        one: jest.fn(),
        map: jest.fn(createMockSlideElementsMap),
    };






    /**
     * mock$のモックは分岐が多いのでコメントと処理を明確に
     */
    mock$ = jest.fn((selectorOrEl, attributes) => {  // ← attributesを第二引数で受け取る
        if (typeof selectorOrEl === 'string' && selectorOrEl.startsWith('<')) {
            let props = attributes || {};
            return {
                _props: { ...props },
                append: jest.fn().mockReturnThis(),
                css: jest.fn().mockReturnThis(),
                attr: jest.fn((key, val) => {
                    if (val === undefined) {
                        if (key === 'class') return props.class || '';
                        return props[key];
                    } else {
                        props[key] = val;
                        return this;
                    }
                }),
                find: jest.fn(() => ({
                    attr: jest.fn().mockReturnThis(),
                })),
            };
        }
        // セレクタ文字列に対応したモック返し
        if (selectorOrEl === '.slider') return mockSliderElement;
        if (selectorOrEl === '.slides') return mockSlidesWrapper;
        if (selectorOrEl === '.slide') return mockSlideElements;



        function mockAttrFunction(mockSrc) {
            return jest.fn(() => mockSrc);
        }

        function mockFindFunction(selectorOrEl) {
            return jest.fn(sel => {
                if (sel === 'img') {
                    return {
                        attr: mockAttrFunction(selectorOrEl.mockSrc),
                    };
                }
                return null;
            });
        }

        // オブジェクトが渡された場合のネストされたモック返し
        if (typeof selectorOrEl === 'object' && selectorOrEl !== null) {
            return {
                find: mockFindFunction(selectorOrEl),
            };
        }

        // デフォルトは sliderElement を返す
        return mockSliderElement;
    });

    // append の呼び出しモックをリセット
    mockSlidesWrapper.append.mockClear();
});

afterEach(() => {
    jest.clearAllMocks();
    jest.useRealTimers();
});

describe('Integlight_FadeSlider2 初期化関連', () => {
    it('初期化時に .fade-effect クラスが付与されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });
        expect(mockSliderElement.addClass).toHaveBeenCalledWith('fade-effect');
    });

    it('画像リストが3枚未満なら複製して3枚にすること', () => {
        // 2枚に減らした slideMocks に差し替え
        slideMocks = [
            { mockSrc: 'img1.jpg' },
            { mockSrc: 'img2.jpg' },
        ];
        mockSlideElements.length = slideMocks.length;

        // map, eq も新しい slideMocks に合わせて差し替え
        mockSlideElements.map = jest.fn(createMockSlideElementsMap);

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.images.length).toBeGreaterThanOrEqual(3);
        expect(inst.images).toEqual(expect.arrayContaining(['img1.jpg', 'img2.jpg']));
    });


    it('初期化時にスライドDOMが3つだけ作成されること', () => {
        const expectedClasses = ['slide-left', 'slide-center', 'slide-right'];

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(mockSlidesWrapper.empty).toHaveBeenCalled();
        expect(mockSlidesWrapper.append).toHaveBeenCalledTimes(3);

        // append に渡されたjQueryモックを取得
        const appendedArgs = mockSlidesWrapper.append.mock.calls.map(call => call[0]);

        // attr('class') を呼んでクラス名を取得
        const classNames = appendedArgs.map(el => el.attr('class'));

        expectedClasses.forEach(expectedClass => {
            expect(classNames.some(c => c.includes(expectedClass))).toBe(true);
        });
    });



    it('初期表示の3つスライドはopacity 1で表示されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.$visible.length).toBe(3);

        inst.$visible.forEach(($s) => {
            expect($s.css).toHaveBeenCalledWith('opacity', 1);
        });
    });

    it('showSlide() 呼び出しでスライドがフェードアウトすること（opacityが0に設定される）', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        // showSlide() を呼ぶと最初に3つのスライドの opacity を0にするはず
        inst.showSlide();

        // 全てのスライドに opacity 0 が設定されているかチェック
        inst.$visible.forEach($slide => {
            expect($slide.css).toHaveBeenCalledWith('opacity', 0);
        });
    });
});
describe('Integlight_FadeSlider2 ブラックボックステスト関連', () => {

    // 書き込みもできるattrモック（内部props保持）
    function createAttrMock(initialSrc) {
        let src = initialSrc;
        const fn = jest.fn((key, val) => {
            if (val === undefined) {
                return src;
            }
            src = val;
            return fn; // チェーン用
        });
        return fn;
    }

    // slideMocks の初期化を下記のように修正
    slideMocks = [
        {
            find: jest.fn(() => ({
                attr: createAttrMock('img1.jpg')
            }))
        },
        {
            find: jest.fn(() => ({
                attr: createAttrMock('img2.jpg')
            }))
        },
        {
            find: jest.fn(() => ({
                attr: createAttrMock('img3.jpg')
            }))
        },
    ];
    it('showSlide() 呼び出しでスライドがフェードアウトすること（opacityが0に設定される）', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        // showSlide() を呼ぶと最初に3つのスライドの opacity を0にするはず
        inst.showSlide();

        // 全てのスライドに opacity 0 が設定されているかチェック
        inst.$visible.forEach($slide => {
            expect($slide.css).toHaveBeenCalledWith('opacity', 0);
        });
    });
    it('showSlide() 呼び出し後、一定時間経過で画像が切り替わること（画像の src が更新される）', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        // 最初のshowSlide呼び出しでフェードアウトが始まる
        inst.showSlide();

        // 最初のフェードアウトで opacity 0 が呼ばれていることを確認（前テストカバーしているが重ねて可）
        inst.$visible.forEach($slide => {
            expect($slide.css).toHaveBeenCalledWith('opacity', 0);
        });

        // changingDuration は settings.changeDuration / 2 なので5 / 2 = 2.5秒（本体コード参照）
        // ただし本体コードでは waitTime = this.changingDuration * 1000 なのでミリ秒換算
        const waitTime = (inst.changingDuration || (inst.displayDuration / 2)) * 1000;

        // タイマーを進める（setTimeoutのコールバックを実行）
        jest.advanceTimersByTime(waitTime);

        // 画像切替が行われる部分の検証（$visible[i].find('img').attr('src', src) の呼び出し検証）
        // $visible[i].find('img').attr はモック関数なので呼び出し記録がある
        inst.$visible.forEach(($slide, i) => {
            const imgMock = $slide.find('img');
            expect(imgMock.attr).toHaveBeenCalledWith('src', expect.any(String));
        });

        // フェードインの opacity 1 も呼ばれているはず
        inst.$visible.forEach($slide => {
            expect($slide.css).toHaveBeenCalledWith('opacity', 1);
        });
    });

});


/*
5	showSlide() 呼び出しでスライドがフェードアウトすること	3つのスライドすべての opacity が0に設定されること。
6	showSlide() 呼び出し後、一定時間経過で画像が切り替わること	タイマーで切替処理が行われ、画像の src 属性が次のものに更新されること。
7	フェードインが開始され、再びスライドの opacity が1になること	画像切替後、スライドの opacity が1に戻されていること。
8	画像配列のループが正しく動作すること	画像インデックスが末尾まで進んだ後、先頭に戻ってループすること。
9	destroy() 呼び出しでタイマーがクリアされること
*/