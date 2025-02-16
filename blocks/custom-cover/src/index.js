import { registerBlockType } from '@wordpress/blocks';
import {
    useBlockProps,
    InspectorControls,
    InnerBlocks,
    MediaUpload,
    MediaUploadCheck,
    PanelColorSettings
} from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.css';
import './editor.css';

registerBlockType('integlight/custom-cover', {
    title: __('Custom Cover', 'integlight'),
    icon: 'cover-image',
    category: 'design',
    supports: {
        align: false // ブロック自体は常に全幅固定。整列オプションは表示しない。
    },
    attributes: {
        innerWidthArticle: {
            type: 'boolean',
            default: false
        },
        url: {
            type: 'string',
            default: ''
        },
        id: {
            type: 'number'
        },
        alt: {
            type: 'string',
            default: ''
        },
        focalPoint: {
            type: 'object',
            default: { x: 0.5, y: 0.5 }
        },
        dimRatio: {
            type: 'number',
            default: 50
        },
        overlayColor: {
            type: 'string'
        },
        backgroundColor: {
            type: 'string'
        },
        textColor: {
            type: 'string'
        }
    },
    edit: ({ attributes, setAttributes }) => {
        const {
            innerWidthArticle,
            url,
            id,
            alt,
            focalPoint,
            dimRatio,
            overlayColor,
            backgroundColor,
            textColor
        } = attributes;

        // 外側コンテナは常に全幅固定（alignfull クラスを付与）
        const blockProps = useBlockProps({
            className: 'wp-block-integlight-custom-cover alignfull',
            style: {
                backgroundColor: backgroundColor,
                backgroundImage: url ? `url(${url})` : undefined,
                backgroundPosition: `${focalPoint.x * 100}% ${focalPoint.y * 100}%`
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
                    </PanelBody>
                    <PanelColorSettings
                        title={__('Color Settings', 'integlight')}
                        initialOpen={false}
                        colorSettings={[
                            {
                                value: backgroundColor,
                                onChange: (newColor) => setAttributes({ backgroundColor: newColor }),
                                label: __('Background Color', 'integlight')
                            },
                            {
                                value: textColor,
                                onChange: (newColor) => setAttributes({ textColor: newColor }),
                                label: __('Text Color', 'integlight')
                            },
                            {
                                value: overlayColor,
                                onChange: (newColor) => setAttributes({ overlayColor: newColor }),
                                label: __('Overlay Color', 'integlight')
                            }
                        ]}
                    />
                </InspectorControls>
                <div {...blockProps}>
                    {/* オーバーレイ（背景画像上に半透明レイヤー） */}
                    {url && (
                        <div className="cover-overlay" style={{
                            backgroundColor: overlayColor ? overlayColor : 'rgba(0,0,0,0)',
                            opacity: dimRatio / 100
                        }} />
                    )}
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
            url,
            focalPoint,
            backgroundColor,
            overlayColor,
            dimRatio
        } = attributes;
        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';
        return (
            <div
                className="wp-block-integlight-custom-cover alignfull"
                style={{
                    backgroundColor: backgroundColor,
                    backgroundImage: url ? `url(${url})` : undefined,
                    backgroundPosition: `${focalPoint.x * 100}% ${focalPoint.y * 100}%`
                }}
            >
                {url && (
                    <div className="cover-overlay" style={{
                        backgroundColor: overlayColor ? overlayColor : 'rgba(0,0,0,0)',
                        opacity: dimRatio / 100
                    }} />
                )}
                <div className={`inner-container ${innerClass}`}>
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
