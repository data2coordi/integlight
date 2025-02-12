import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ColorPicker } from '@wordpress/components';
import './style.css';
import './editor.css';

registerBlockType('integlight/text-flow-animation', {
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps({ className: "text-flow-editor" });

        return (
            <>
                <InspectorControls>
                    <PanelBody title="テキスト設定">
                        <RangeControl
                            label="フォントサイズ"
                            value={attributes.fontSize}
                            onChange={(value) => setAttributes({ fontSize: value })}
                            min={10}
                            max={100}
                        />
                        <ColorPicker
                            label="カラー"
                            color={attributes.color}
                            onChangeComplete={(value) => setAttributes({ color: value.hex })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <RichText
                        tagName="p"
                        value={attributes.content}
                        onChange={(content) => setAttributes({ content })}
                        placeholder="テキストを入力..."
                        style={{
                            fontSize: `${attributes.fontSize}px`,
                            color: attributes.color
                        }}
                    />
                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        const blockProps = useBlockProps.save({ className: "text-flow-animation" });

        return (
            <div {...blockProps} style={{ fontSize: `${attributes.fontSize}px`, color: attributes.color }}>
                <div className="loop_wrap">
                    <div>{attributes.content}&nbsp;</div>
                    <div>{attributes.content}&nbsp;</div>
                    <div>{attributes.content}&nbsp;</div>
                </div>
            </div>
        );
    }
});
