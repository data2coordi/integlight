// blocks/gfontawesome/src/index.js

const { registerFormatType, toggleFormat } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;
const { createElement } = wp.element;
const { addFilter } = wp.hooks;

/* --- インラインフォーマットの登録 --- */
const FontAwesomeIconButton = (props) => {
    const { isActive, value, onChange } = props;

    const applyFormat = () => {
        onChange(
            toggleFormat(value, {
                type: 'integlight/font-awesome-icon',
                attributes: { class: 'fas fa-coffee' },
            })
        );
    };

    return createElement(RichTextToolbarButton, {
        icon: 'admin-customizer', // Dashicon のアイコン。必要に応じて変更してください。
        title: 'コーヒーアイコンを挿入',
        onClick: applyFormat,
        isActive: isActive,
    });
};

registerFormatType('integlight/font-awesome-icon', {
    title: 'Font Awesome Icon',
    tagName: 'i',
    className: null,  // attributes で class を指定するため不要
    attributes: {
        class: 'class',
    },
    edit: (props) => createElement(FontAwesomeIconButton, props),
});

/* --- editor.BlockEdit フィルターで allowedFormats を上書き --- */
const withFontAwesomeAllowedFormats = (BlockEdit) => {
    return (props) => {
        if (props.name === 'core/paragraph' || props.name === 'core/heading') {
            // 既存の allowedFormats がなければデフォルト値を設定
            const defaultFormats = props.allowedFormats || ['core/bold', 'core/italic', 'core/link'];
            // Font Awesome フォーマットを追加
            const allowedFormats = [...defaultFormats, 'integlight/font-awesome-icon'];
            return createElement(BlockEdit, { ...props, allowedFormats });
        }
        return createElement(BlockEdit, props);
    };
};

addFilter(
    'editor.BlockEdit',
    'integlight/with-fontawesome-allowed-formats',
    withFontAwesomeAllowedFormats
);
