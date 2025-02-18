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

registerBlockType('integlight/speech-bubble', {
    edit: (props) => {
        const {
            attributes: { content, imageUrl, imageAlt, backgroundColor, textColor, reverse },
            setAttributes,
            className
        } = props;

        const onSelectImage = (media) => {
            setAttributes({
                imageUrl: media.url,
                imageAlt: media.alt || '顔画像'
            });
        };

        // 編集画面用のブロックプロパティ（背景色・テキスト色を inline style に反映）
        const contentBlockProps = useBlockProps({
            className: 'speech-bubble__content',
            style: {
                backgroundColor: backgroundColor,
                color: textColor
            }
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title="画像設定" initialOpen={true}>
                        {imageUrl ? (
                            <div>
                                <img src={imageUrl} alt={imageAlt} style={{ width: '100%' }} />
                                <Button
                                    onClick={() => setAttributes({ imageUrl: '', imageAlt: '' })}
                                    isLink
                                    isDestructive
                                >
                                    画像を削除
                                </Button>
                            </div>
                        ) : (
                            <MediaUploadCheck>
                                <MediaUpload
                                    onSelect={onSelectImage}
                                    allowedTypes={['image']}
                                    render={({ open }) => (
                                        <Button onClick={open} isPrimary>
                                            画像を選択
                                        </Button>
                                    )}
                                />
                            </MediaUploadCheck>
                        )}
                    </PanelBody>
                    <PanelBody title="レイアウト設定" initialOpen={false}>
                        <ToggleControl
                            label="画像と吹き出しの位置を反転する"
                            checked={reverse}
                            onChange={(newVal) => setAttributes({ reverse: newVal })}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className={`${className} wp-block speech-bubble ${reverse ? 'speech-bubble--reverse' : ''}`}>
                    {imageUrl && (
                        <div className="speech-bubble__image">
                            <img src={imageUrl} alt={imageAlt} />
                        </div>
                    )}
                    <div {...contentBlockProps}>
                        <RichText
                            tagName="p"
                            onChange={(newContent) => setAttributes({ content: newContent })}
                            value={content}
                            placeholder="ここにメッセージを入力"
                        />
                    </div>
                </div>
            </>
        );
    },

    save: (props) => {
        const {
            attributes: { content, imageUrl, imageAlt, backgroundColor, textColor, reverse }
        } = props;

        // 保存側でも useBlockProps.save を使って inline style を出力
        const contentBlockProps = useBlockProps.save({
            className: 'speech-bubble__content',
            style: {
                backgroundColor: backgroundColor,
                color: textColor
            }
        });

        return (
            <div className={"speech-bubble" + (reverse ? " speech-bubble--reverse" : "")}>
                {imageUrl && (
                    <div className="speech-bubble__image">
                        <img src={imageUrl} alt={imageAlt} />
                    </div>
                )}
                <div {...contentBlockProps}>
                    <RichText.Content tagName="p" value={content} />
                </div>
            </div>
        );
    }
});
