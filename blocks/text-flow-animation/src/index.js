const { registerBlockType } = wp.blocks;
const { RichText, InspectorControls, ColorPalette, FontSizePicker } = wp.blockEditor || wp.editor;
const { PanelBody } = wp.components;

registerBlockType('integlight/text-flow-animation', {
    title: '【Integlight】テキスト流れるアニメーション',
    icon: 'editor-alignleft',
    category: 'widgets',
    attributes: {
        content: {
            type: 'string',
            source: 'html',
            selector: 'p'
        },
        color: {
            type: 'string',
            default: '#000000'
        },
        fontSize: {
            type: 'number',
            default: 16
        }
    },

    edit: ({ attributes, setAttributes, isSelected }) => {
        const { content, color, fontSize } = attributes;
        const className = isSelected ? "text-flow-animation edit-mode" : "text-flow-animation";

        return (
            <>
                <InspectorControls>
                    <PanelBody title="テキスト設定">
                        <p>テキストカラー</p>
                        <ColorPalette
                            value={color}
                            onChange={(newColor) => setAttributes({ color: newColor || '#000000' })}
                        />
                        <p>フォントサイズ</p>
                        <FontSizePicker
                            value={fontSize || 16}
                            onChange={(newFontSize) => {
                                if (newFontSize !== null) {
                                    setAttributes({ fontSize: newFontSize });
                                }
                            }}
                            min={10}
                            max={100}
                        />
                    </PanelBody>
                </InspectorControls>
                <RichText
                    tagName="p"
                    className={className}
                    style={{ color, fontSize }}
                    value={content}
                    onChange={(newContent) => setAttributes({ content: newContent })}
                    placeholder="ここにテキストを入力…"
                />
            </>
        );
    },

    save: ({ attributes }) => {
        return (
            <div className="text-flow-animation-container">
                <div
                    className="text-flow-animation"
                    style={{ color: attributes.color, fontSize: attributes.fontSize }}
                >
                    {attributes.content}
                </div>
            </div>
        );
    }
});
