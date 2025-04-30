// jest.setup.js

import '@testing-library/jest-dom';
import React from 'react';

// モック wp オブジェクト
global.wp = {
    element: require('@wordpress/element'),
    richText: {
        insert: jest.fn((value, shortcode) => value + shortcode),
    },
    blockEditor: {
        RichTextToolbarButton: ({ icon, title, onClick }) => (
            <button onClick={onClick} aria-label={title}>{icon}</button>
        ),
    },
    components: {
        Modal: ({ children, onRequestClose }) => (
            <div data-testid="modal">
                <button onClick={onRequestClose}>Close</button>
                {children}
            </div>
        ),
        Button: ({ onClick, children }) => (
            <button onClick={onClick}>{children}</button>
        ),
        TextControl: ({ label, value, onChange }) => (
            <label>
                {label}
                <input
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    aria-label={label}
                />
            </label>
        ),
        Spinner: () => <div>Loading...</div>,
    },
};
import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { FontAwesomeSearchButton } from '../../../blocks/gfontawesome/src/index.js';

describe('FontAwesomeSearchButton', () => {
    const mockOnChange = jest.fn();
    const mockInsert = jest.fn((value, shortcode) => value + shortcode);

    beforeEach(() => {
        // モックをクリア
        mockOnChange.mockClear();
        mockInsert.mockClear();
        wp.richText.insert = mockInsert;

        // fetch をモック
        global.fetch = jest.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve({
                    solid: ['fa-home', 'fa-user', 'fa-cog'],
                    brands: ['fa-twitter', 'fa-facebook']
                }),
            })
        );
    });

    afterEach(() => {
        global.fetch.mockRestore?.();
    });

    it('renders toolbar button', () => {
        render(<FontAwesomeSearchButton value="" onChange={mockOnChange} />);
        expect(screen.getByRole('button', { name: 'Font Awesome Icon Search' })).toBeInTheDocument();
    });

    it('opens modal on button click and loads icons', async () => {
        render(<FontAwesomeSearchButton value="Hello" onChange={mockOnChange} />);
        fireEvent.click(screen.getByRole('button', { name: 'Font Awesome Icon Search' }));

        expect(await screen.findByTestId('modal')).toBeInTheDocument();
        expect(global.fetch).toHaveBeenCalledWith(
            '/wp-content/themes/integlight/blocks/gfontawesome/fontawesome-icons.json'
        );
    });

    it('filters icons based on search input', async () => {
        render(<FontAwesomeSearchButton value="" onChange={mockOnChange} />);
        fireEvent.click(screen.getByRole('button', { name: 'Font Awesome Icon Search' }));

        await screen.findByText(/solid/i); // カテゴリ表示確認

        const input = screen.getByLabelText('Search');
        fireEvent.change(input, { target: { value: 'home' } });

        expect(await screen.findByText((_, el) => el?.className.includes('fa-home'))).toBeInTheDocument();
    });

    it('inserts shortcode when icon is clicked', async () => {
        render(<FontAwesomeSearchButton value="start-" onChange={mockOnChange} />);
        fireEvent.click(screen.getByRole('button', { name: 'Font Awesome Icon Search' }));

        await screen.findByText(/solid/i);

        const iconButton = screen.getAllByRole('button').find(btn =>
            btn.querySelector('i.fas.fa-home')
        );

        fireEvent.click(iconButton);

        expect(mockInsert).toHaveBeenCalledWith('start-', '[fontawesome icon=fa-home]');
        expect(mockOnChange).toHaveBeenCalledWith('start-[fontawesome icon=fa-home]');
    });
});
