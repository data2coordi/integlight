const { registerFormatType } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;
const { Fragment } = wp.element;
const { createElement } = wp.element;

// Font Awesome アイコンのリスト
const icons = [
    'fa-home',
    'fa-user',
    'fa-cog',
    'fa-heart',
    'fa-star',
];

const FontAwesomeButton = ({ isActive, value, onChange }) => {
    const insertIcon = (icon) => {
        const iconTag = `<i class="fas ${icon}"></i>`;
        const newValue = wp.richText.insert(value, iconTag);
        onChange(newValue);
    };

    return (
        <Fragment>
            {icons.map((icon, index) => (
                <RichTextToolbarButton
                    key={index}
                    icon={createElement('i', { className: `fas ${icon}` })}
                    title={`Insert ${icon}`}
                    onClick={() => insertIcon(icon)}
                    isActive={isActive}
                />
            ))}
        </Fragment>
    );
};

// フォーマットタイプを登録
registerFormatType('gfontawesome/icon', {
    title: 'FontAwesome',
    tagName: 'i',
    className: 'fas',
    edit: FontAwesomeButton,
});
