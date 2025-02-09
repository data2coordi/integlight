(function (wp) {
    var registerBlockType = wp.blocks.registerBlockType;
    var createElement = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var MediaUpload = wp.blockEditor.MediaUpload || wp.editor.MediaUpload;
    var Button = wp.components.Button;
    var InspectorControls = wp.blockEditor.InspectorControls || wp.editor.InspectorControls;

    registerBlockType('integlight/image-flow-animation', {
        title: '画像流れるアニメーション',
        icon: 'format-image',
        category: 'widgets',
        attributes: {
            imageUrl: {
                type: 'string',
                default: ''
            },
            altText: {
                type: 'string',
                default: ''
            }
        },
        edit: function (props) {
            var attributes = props.attributes;
            var imageUrl = attributes.imageUrl;
            var altText = attributes.altText;

            function onSelectImage(media) {
                props.setAttributes({
                    imageUrl: media.url,
                    altText: media.alt || ''
                });
            }

            return createElement(Fragment, {},
                createElement(InspectorControls, {},
                    createElement('div', { style: { padding: '10px' } }, '画像が流れるブロックです。')
                ),
                imageUrl ?
                    // 編集画面ではシンプルに1組のみ表示（プレビュー用）
                    createElement('article', null,
                        createElement('div', { className: 'loop_wrap' },
                            createElement('div', null,
                                createElement('img', {
                                    src: imageUrl,
                                    alt: altText,
                                    className: 'image-flow-animation'
                                })
                            )
                        )
                    )
                    :
                    createElement(MediaUpload, {
                        onSelect: onSelectImage,
                        allowedTypes: ['image'],
                        value: imageUrl,
                        render: function (obj) {
                            return createElement(Button, {
                                onClick: obj.open,
                                isPrimary: true
                            }, '画像を選択');
                        }
                    })
            );
        },
        save: function (props) {
            var attributes = props.attributes;
            var imageUrl = attributes.imageUrl;
            var altText = attributes.altText;

            return imageUrl ?
                createElement('article', null,
                    createElement('div', { className: 'loop_wrap' },
                        // 1組目の画像セット（ここでは同じ画像を2枚出力していますが、
                        // 必要に応じて複数枚用意してください）
                        createElement('div', null,
                            createElement('img', {
                                src: imageUrl,
                                alt: altText,
                                className: 'image-flow-animation'
                            }),
                            createElement('img', {
                                src: imageUrl,
                                alt: altText,
                                className: 'image-flow-animation'
                            })
                        ),
                        // 2組目（クローン）の画像セット
                        createElement('div', null,
                            createElement('img', {
                                src: imageUrl,
                                alt: altText,
                                className: 'image-flow-animation'
                            }),
                            createElement('img', {
                                src: imageUrl,
                                alt: altText,
                                className: 'image-flow-animation'
                            })
                        )
                    )
                ) : null;
        }
    });
})(window.wp);
