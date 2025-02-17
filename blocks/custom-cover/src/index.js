import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
    InnerBlocks,
    MediaUpload,
    MediaUploadCheck
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.css';
import './editor.css';

registerBlockType('integlight/custom-cover', {
    edit: ({ attributes, setAttributes }) => {
        const {
            innerWidthArticle,
            url,
            id,
            alt
        } = attributes;

        const blockProps = useBlockProps({
            className: 'wp-block-integlight-custom-cover alignfull',
            style: {
                backgroundImage: url ? `url(${url})` : undefined
            }
        });

        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Cover Settings', 'integlight')}>
                        <ToggleControl
                            label={__('Use Article Width for Inner Content', 'integlight')}
                            checked={innerWidthArticle}
                            onChange={() => setAttributes({ innerWidthArticle: !innerWidthArticle })}
                        />
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) =>
                                    setAttributes({
                                        url: media.url,
                                        id: media.id,
                                        alt: media.alt
                                    })
                                }
                                allowedTypes={['image']}
                                value={id}
                                render={({ open }) => (
                                    <Button onClick={open} isPrimary>
                                        {!url ? __('Upload Background Image', 'integlight') : __('Change Background Image', 'integlight')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        {url && (
                            <Button
                                onClick={() => setAttributes({ url: '', id: undefined, alt: '' })}
                                isSecondary
                                style={{ marginTop: '10px' }}
                            >
                                {__('Remove Background Image', 'integlight')}
                            </Button>
                        )}
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
        const {
            innerWidthArticle,
            url
        } = attributes;

        const blockProps = useBlockProps.save({
            className: 'wp-block-integlight-custom-cover alignfull',
            style: {
                backgroundImage: url ? `url(${url})` : undefined,
            }
        });

        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';

        return (
            <div {...blockProps}>
                <div className={`inner-container ${innerClass}`}>
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
