const { Fragment, useState, useEffect } = wp.element;
const { registerFormatType, insert } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;
const { Modal, Button, TextControl, Spinner } = wp.components;

const FontAwesomeSearchButton = ({ value, onChange }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [icons, setIcons] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (isModalOpen && icons.length === 0) {
            setLoading(true);
            fetch('/wp-content/themes/integlight/blocks/gfontawesome/fontawesome-icons.json')
                .then(response => response.json())
                .then(data => {
                    setIcons(data.icons);
                    setLoading(false);
                })
                .catch(error => {
                    console.error('アイコン取得エラー:', error);
                    setLoading(false);
                });
        }
    }, [isModalOpen]);

    const filteredIcons = icons.filter(icon =>
        icon.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // 選択されたアイコンをショートコード形式で挿入する（例: [fontawesome icon="fa-home"]）
    const insertIcon = (icon) => {
        const shortcode = `[fa icon="${icon}"]`;
        const newValue = insert(value, shortcode);
        onChange(newValue);
        setIsModalOpen(false);
    };

    return (
        <Fragment>
            <RichTextToolbarButton
                icon="search"
                title="Font Awesome アイコンを検索"
                onClick={() => setIsModalOpen(true)}
            />
            {isModalOpen && (
                <Modal
                    title="Font Awesome アイコンを検索"
                    onRequestClose={() => setIsModalOpen(false)}
                >
                    <TextControl
                        label="検索"
                        value={searchTerm}
                        onChange={(newValue) => setSearchTerm(newValue)}
                        placeholder="例: home, user, cog..."
                    />
                    {loading ? (
                        <Spinner />
                    ) : (
                        <div
                            className="fa-icons-grid"
                            style={{
                                display: 'flex',
                                flexWrap: 'wrap',
                                gap: '10px',
                                marginTop: '10px',
                            }}
                        >
                            {filteredIcons.map((icon, index) => (
                                <Button
                                    key={index}
                                    onClick={() => insertIcon(icon)}
                                    style={{ padding: '10px' }}
                                >
                                    <i className={`fas ${icon}`} style={{ fontSize: '24px' }}></i>
                                </Button>
                            ))}
                        </div>
                    )}
                </Modal>
            )}
        </Fragment>
    );
};

registerFormatType('fontawesome/icon', {
    title: 'Font Awesome',
    tagName: 'span',
    // 独自のクラス名を付与することで衝突を回避
    className: 'gfontawesome-shortcode',
    edit: (props) => <FontAwesomeSearchButton {...props} />,
});
