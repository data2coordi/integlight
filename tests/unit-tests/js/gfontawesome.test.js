// /home/h95mori/dev_wp_env/html/wp-content/themes/integlight/tests/unit-tests/js/gfontawesome.test.js

import '@testing-library/jest-dom';
import { render, screen, fireEvent } from '@testing-library/react';

// --- WordPress 環境の最小限モック ---
// beforeAll で一度だけ定義
beforeAll(() => {
    global.wp = {
        // React の基本機能 (useState, useEffect, Fragment) のために必要
        element: require('@wordpress/element'),
        // アイコン挿入機能のモック
        richText: {
            insert: jest.fn((value, content) => value + content), // 最も単純な挿入動作
            registerFormatType: jest.fn(), // index.js 読み込みに必要
        },
        // ツールバーボタンのモック (単純な button)
        blockEditor: {
            RichTextToolbarButton: ({ title, onClick }) => (
                <button data-testid="toolbar-button" onClick={onClick} aria-label={title || 'Toolbar Button'}>
                    Toolbar Button
                </button>
            ),
        },
        // モーダルと内部コンポーネントのモック (単純な要素)
        components: {
            Modal: ({ children, title }) => <div data-testid="modal" aria-label={title}>{children}</div>,
            Button: ({ onClick, children, 'aria-label': ariaLabel }) => <button onClick={onClick} aria-label={ariaLabel}>{children}</button>,
            TextControl: ({ label, value, onChange }) => <input type="text" aria-label={label} value={value || ''} onChange={(e) => onChange(e.target.value)} />,
            Spinner: () => <div data-testid="spinner">Loading...</div>,
        },
    };

    // fetch API のモック (固定レスポンス)
    global.fetch = jest.fn(() =>
        Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ solid: ['fa-home', 'fa-user'] }), // テストに必要な最小限のアイコン
        })
    );
});

// --- テスト本体 ---
describe('FontAwesomeSearchButton (Simplified)', () => {
    let FontAwesomeSearchButton;
    const mockOnChange = jest.fn();

    // 各テスト前にコンポーネントを動的にインポートし、モックをクリア
    beforeEach(async () => {
        // モジュールキャッシュをリセットしてからインポート
        jest.resetModules();
        const module = await import('../../../blocks/gfontawesome/src/index.js');
        FontAwesomeSearchButton = module.FontAwesomeSearchButton;

        // モック関数の呼び出し履歴をクリア
        mockOnChange.mockClear();
        global.wp.richText.insert.mockClear();
        global.fetch.mockClear();
    });

    // Test 1: ツールバーボタンが表示され、クリックでモーダルが開くか
    it('ツールバーボタンが表示され、クリックでモーダルが開くこと', () => {
        render(<FontAwesomeSearchButton value="Initial" onChange={mockOnChange} />);

        // ツールバーボタンが表示されていることを確認
        const toolbarButton = screen.getByTestId('toolbar-button');
        expect(toolbarButton).toBeInTheDocument();

        // ボタンをクリック
        fireEvent.click(toolbarButton);

        // モーダルが表示されることを確認
        expect(screen.getByTestId('modal')).toBeInTheDocument();
    });

    // Test 2: アイコンをクリックすると onChange が呼ばれるか (fetch も含む)
    it('アイコンをクリックすると onChange が正しい値で呼ばれること', async () => {
        const initialValue = "Initial value";
        render(<FontAwesomeSearchButton value={initialValue} onChange={mockOnChange} />);

        // ツールバーボタンをクリックしてモーダルを開く
        fireEvent.click(screen.getByTestId('toolbar-button'));

        // fetch が呼ばれたことを確認 (モーダル表示時に fetch が走るため)
        expect(global.fetch).toHaveBeenCalledTimes(1);

        // アイコンボタンが表示されるのを待つ (fetch の非同期処理後)
        const iconButton = await screen.findByRole('button', { name: 'fa-home' });
        expect(iconButton).toBeInTheDocument();

        // アイコンボタンをクリック
        fireEvent.click(iconButton);

        // wp.richText.insert が呼ばれたか確認
        expect(global.wp.richText.insert).toHaveBeenCalledTimes(1);
        expect(global.wp.richText.insert).toHaveBeenCalledWith(initialValue, '[fontawesome icon=fa-home]');

        // onChange が insert の結果で呼ばれたか確認
        expect(mockOnChange).toHaveBeenCalledTimes(1);
        expect(mockOnChange).toHaveBeenCalledWith(initialValue + '[fontawesome icon=fa-home]'); // insert モックの挙動に基づく

        // モーダルが閉じていることを確認
        expect(screen.queryByTestId('modal')).not.toBeInTheDocument();
    });
});
