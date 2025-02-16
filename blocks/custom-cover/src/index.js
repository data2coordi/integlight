import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// CSS のインポート（ビルド後は build/ に出力されます）
import './style.css';
import './editor.css';

registerBlockType('integlight/custom-cover', {
    title: __('Custom Cover', 'integlight'),
    icon: 'cover-image',
    category: 'design',
    supports: {
        align: ['full'] // 外側コンテナは全幅固定
    },
    attributes: {
        // 内側の幅を記事幅（true）か全幅（false）かで制御
        innerWidthArticle: {
            type: 'boolean',
            default: false
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const { innerWidthArticle } = attributes;
        // 外側コンテナは常に全幅
        const blockProps = useBlockProps({
            className: 'wp-block-integlight-custom-cover alignfull'
        });
        // 内側コンテンツのクラスを切り替え
        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Inner Content Width', 'integlight')}>
                        <ToggleControl
                            label={__('記事幅にする', 'integlight')}
                            checked={innerWidthArticle}
                            onChange={() => setAttributes({ innerWidthArticle: !innerWidthArticle })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <div className={`inner-container ${innerClass}`}>
                        <InnerBlocks />
                    </div>
                </div>
            </>
        );
    },
    save: ({ attributes }) => {
        const { innerWidthArticle } = attributes;
        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';
        return (
            <div className="wp-block-integlight-custom-cover alignfull">
                <div className={`inner-container ${innerClass}`}>
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
