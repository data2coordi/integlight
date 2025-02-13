const { registerFormatType } = wp.richText;
const { RichTextToolbarButton } = wp.blockEditor;

// 右寄せフォーマットを登録
registerFormatType('integlight/right-align', {
    title: 'Align Right Z',
    tagName: 'span',
    className: 'alignright',
    edit({ isActive, value, onChange }) {
        return (
            <RichTextToolbarButton
                icon="editor-alignright"
                title="Align Right Z"
                isActive={isActive}
                onClick={() => {
                    onChange(
                        wp.richText.toggleFormat(value, {
                            type: 'integlight/right-align',
                        })
                    );
                }}
            />
        );
    },
});
