import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// ✅ CSSをインポート
import './style.css';
import './editor.css';

registerBlockType('integlight/custom-cover', {
    title: __('Custom Cover', 'integlight'),
    icon: 'cover-image',
    category: 'design',
    attributes: {
        postWidth: {
            type: 'boolean',
            default: false,
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const { postWidth } = attributes;
        const blockProps = useBlockProps({
            className: postWidth ? 'align-post' : '',
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Settings', 'integlight')}>
                        <ToggleControl
                            label={__('Post Width', 'integlight')}
                            checked={postWidth}
                            onChange={() => setAttributes({ postWidth: !postWidth })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <p>{__('This is a custom cover block.', 'integlight')}</p>
                </div>
            </>
        );
    },
    save: ({ attributes }) => {
        return (
            <div className={`wp-block-integlight-custom-cover ${attributes.postWidth ? 'align-post' : ''}`}>
                <p>{__('This is a saved custom cover block.', 'integlight')}</p>
            </div>
        );
    }
});
