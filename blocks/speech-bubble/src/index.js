// 注意: useBlockProps は @wordpress/block-editor からインポートする
import './editor.css';
import './style.css';

import { registerBlockType } from '@wordpress/blocks';
import {
    InspectorControls,
    RichText,
    MediaUpload,
    MediaUploadCheck,
    useBlockProps
} from '@wordpress/block-editor';
import { PanelBody, Button, ToggleControl } from '@wordpress/components';

import { __ } from '@wordpress/i18n';



registerBlockType('integlight/speech-bubble', {
    edit: (props) => {
        const {
            attributes: { content, imageUrl, imageAlt, imageCaption, backgroundColor, textColor, reverse },
            setAttributes,
            className
        } = props;

        const onSelectImage = (media) => {
            setAttributes({
                imageUrl: media.url,
                imageAlt: media.alt || __('faceimage', 'integlight')
            });
        };

        // 編集画面用のブロックプロパティ（背景色・テキスト色を inline style に反映）
        const contentBlockProps = useBlockProps({
            className: 'speech-bubble__content',
            style: {
                color: textColor,
                ...(backgroundColor &&
                    (backgroundColor.startsWith('#') ||
                        backgroundColor.startsWith('linear-gradient') ||
                        backgroundColor.startsWith('radial-gradient')
                    )
                    ? { backgroundColor }
                    : {}
                )
            }
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__("Image setting", "integlight")} initialOpen={true}>
                        {imageUrl ? (
                            <div>
                                <img src={imageUrl} alt={imageAlt} style={{ width: '100%' }} />
                                <Button
                                    onClick={() => setAttributes({ imageUrl: '', imageAlt: '' })}
                                    isLink
                                    isDestructive
                                >

                                    {__("Delete image", "integlight")}
                                </Button>
                            </div>
                        ) : (
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={onSelectImage}
                                    allowedTypes={['image']}
                                    render={({ open }) => (
                                        <Button onClick={open} isPrimary>
                                            {__("Select image", "integlight")}
                                        </Button>
                                    )}
                                />
                            </MediaUploadCheck>
                        )}
                    </PanelBody>
                    <PanelBody title={__("layout setting", "integlight")} initialOpen={false}>
                        <ToggleControl
                            label={__("Reverse the positions of the image and speech bubble.", "integlight")}
                            checked={reverse}
                            onChange={(newVal) => setAttributes({ reverse: newVal })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className={`${className} wp-block integlight-speech-bubble ${reverse ? "integlight-speech-bubble--reverse" : "integlight-speech-bubble--normal"}`}>


                    {imageUrl && (
                        <figure className="speech-bubble__image">
                            <img src={imageUrl} alt={imageAlt} />
                            <RichText
                                tagName="figcaption"
                                className="speech-bubble__image-caption"
                                onChange={(newCaption) => setAttributes({ imageCaption: newCaption })}
                                value={imageCaption}
                                placeholder={__("Enter caption here.", "integlight")}
                            />
                        </figure>
                    )}
                    <div {...contentBlockProps}>
                        <RichText
                            tagName="p"
                            onChange={(newContent) => setAttributes({ content: newContent })}
                            value={content}
                            placeholder={__("Enter message here.", "integlight")}
                        />
                    </div>
                </div>
            </>
        );
    },

    save: (props) => {
        const {
            attributes: { content, imageUrl, imageAlt, imageCaption, backgroundColor, textColor, reverse }

        } = props;

        // 保存側でも useBlockProps.save を使って inline style を出力
        const contentBlockProps = useBlockProps.save({
            className: 'speech-bubble__content',
            style: {
                color: textColor,
                ...(backgroundColor &&
                    (backgroundColor.startsWith('#') ||
                        backgroundColor.startsWith('linear-gradient') ||
                        backgroundColor.startsWith('radial-gradient')
                    )
                    ? { backgroundColor }
                    : {}
                )
            }
        });

        return (

            <div className={`integlight-speech-bubble ${reverse ? "integlight-speech-bubble--reverse" : "integlight-speech-bubble--normal"}`}>


                {imageUrl && (
                    <figure className="speech-bubble__image">
                        <img src={imageUrl} alt={imageAlt} />
                        <RichText.Content
                            tagName="figcaption"
                            className="speech-bubble__image-caption"
                            value={imageCaption}
                        />
                    </figure>
                )}
                <div {...contentBlockProps}>
                    <RichText.Content tagName="p" value={content} />
                </div>
            </div>
        );
    }
});
