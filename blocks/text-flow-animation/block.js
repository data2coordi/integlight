(function (wp) {
    // wp.blocks, wp.element, wp.blockEditor（または wp.editor） をグローバルから取得
    var registerBlockType = wp.blocks.registerBlockType;
    var createElement = wp.element.createElement;
    // wp.blockEditor は WordPress 5.3以降、旧バージョンの場合は wp.editor を使用してください。
    var RichText = wp.blockEditor ? wp.blockEditor.RichText : wp.editor.RichText;

    registerBlockType('integlight/text-flow-animation',
        {
            title: '【Integlight】テキスト流れるアニメーション',
            icon: 'editor-alignleft',
            category: 'widgets', // 必要に応じてカテゴリーを変更してください
            attributes: {
                content: {
                    type: 'string',
                    source: 'html',
                    selector: 'p'
                }
            },
            edit: function (props) {
                var content = props.attributes.content;
                return createElement(RichText,
                    {
                        tagName: 'p',
                        className: 'text-flow-animation',
                        value: content,
                        onChange: function (newContent) {
                            props.setAttributes({
                                content: newContent
                            });
                        },
                        placeholder: 'ここにテキストを入力…'
                    });
            },
            save: function (props) {
                return createElement(RichText.Content,
                    {
                        tagName: 'p',
                        className: 'text-flow-animation',
                        value: props.attributes.content
                    });
            }
        });
})(window.wp);





