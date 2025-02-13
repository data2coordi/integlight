const { registerFormatType } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;
const { Fragment } = wp.element;

// Font Awesome のアイコンリスト
const icons = [
    'home',
    'user',
    'cog',
    'heart',
    'star',
];

const FontAwesomeButton = ({ value, onChange }) => {
    const insertIcon = (icon) => {
        const shortcode = `[fa icon="${icon}"]`;
        onChange(wp.richText.insert(value, shortcode)); // ショートコードをエディタに挿入
    };

    return (
        <Fragment>
            {icons.map((icon, index) => (
                <RichTextToolbarButton
                    key={index}
                    icon={`fas fa-${icon}`} // ツールバーのアイコン
                    title={`Insert ${icon}`}
                    onClick={() => insertIcon(icon)}
                />
            ))}
        </Fragment>
    );
};

// フォーマットタイプを登録
registerFormatType('gfontawesome/icon', {
    title: 'FontAwesome',
    tagName: 'span',
    className: 'fa-shortcode',
    edit: FontAwesomeButton,
});
