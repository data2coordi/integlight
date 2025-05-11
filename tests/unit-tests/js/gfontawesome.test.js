import '@testing-library/jest-dom';
import React, { useState, useEffect } from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';

// モック
global.fetch = jest.fn(() =>
    Promise.resolve({
        ok: true,
        json: () => Promise.resolve({ solid: ['fa-home', 'fa-user'] }),
    })
);

global.wp = {
    element: { Fragment: React.Fragment },
    blockEditor: {
        RichTextToolbarButton: ({ onClick }) => (
            <button data-testid="toolbar-button" onClick={onClick}>Toolbar Button</button>
        ),
    },
    components: {
        Modal: ({ children }) => <div data-testid="modal">{children}</div>,
        Button: ({ onClick, children }) => <button onClick={onClick}>{children}</button>,
        TextControl: ({ value, onChange }) => <input value={value} onChange={(e) => onChange(e.target.value)} />,
        Spinner: () => <div>Loading...</div>,
    },
};

// テスト対象コンポーネント
const FontAwesomeSearchButton = ({ value, onChange }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [iconsData, setIconsData] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (isModalOpen && !iconsData) {
            setLoading(true);
            fetch('/icons.json')
                .then((res) => res.json())
                .then((data) => {
                    setIconsData(data);
                    setLoading(false);
                })
                .catch(() => setLoading(false));
        }
    }, [isModalOpen, iconsData]);

    const insertIcon = (icon) => {
        const shortcode = `[fontawesome icon=${icon}]`;
        onChange(value + shortcode);
        setIsModalOpen(false);
    };

    return (
        <>
            <button data-testid="toolbar-button" onClick={() => setIsModalOpen(true)}>Open Modal</button>
            {isModalOpen && (
                <div data-testid="modal">
                    <input type="text" value="" onChange={() => { }} />
                    {loading ? <div>Loading...</div> : (
                        <div>
                            {iconsData?.solid.map((icon) => (
                                <button key={icon} onClick={() => insertIcon(icon)}>{icon}</button>
                            ))}
                        </div>
                    )}
                </div>
            )}
        </>
    );
};

// テスト
describe('FontAwesomeSearchButton', () => {
    it('ツールバーボタンが表示され、クリックでモーダルが開くこと', async () => {
        render(<FontAwesomeSearchButton value="" onChange={() => { }} />);

        // ボタンが表示されているか確認
        const button = screen.getByTestId('toolbar-button');
        expect(button).toBeInTheDocument();

        fireEvent.click(button);
        expect(await screen.findByTestId('modal')).toBeInTheDocument();
    });

    it('アイコンをクリックすると onChange が呼ばれること', async () => {
        const mockOnChange = jest.fn();
        render(<FontAwesomeSearchButton value="" onChange={mockOnChange} />);

        // モーダルを開く
        fireEvent.click(screen.getByTestId('toolbar-button'));

        // アイコンボタンが表示されるのを待つ
        await waitFor(() => screen.getByText('fa-home'));

        // アイコンボタンをクリック
        fireEvent.click(screen.getByText('fa-home'));

        // onChange が呼ばれているか確認
        expect(mockOnChange).toHaveBeenCalledWith('[fontawesome icon=fa-home]');
    });
});
