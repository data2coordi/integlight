const { registerPlugin } = wp.plugins;
const { PluginToolbar } = wp.editPost;
const { ToolbarButton } = wp.components;
const { BlockControls, registerFormatType, RichText } = wp.blockEditor;
const { Fragment } = wp.element;

// 右寄せのフォーマットを追加
registerFormatType('my-theme/align-right', {
    title: 'Align Right',
    tagName: 'div',
    className: 'alignright',
    edit({ value, onChange }) {
        return (
            <BlockControls>
                <PluginToolbar>
                    <ToolbarButton
                        icon="editor-alignright"
                        label="Align Right"
                        onClick={() => {
                            // 右寄せを切り替える
                            onChange(
                                value === 'right' ? 'left' : 'right'
                            );
                        }}
                    />
                </PluginToolbar>
            </BlockControls>
        );
    },
});
