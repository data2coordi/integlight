const { registerBlockType } = wp.blocks;
const { RichText } = wp.blockEditor || wp.editor;

registerBlockType('integlight/text-flow-animation', {
    title: '【Integlight】テキスト流れるアニメーション',
    icon: 'editor-alignleft',
    category: 'widgets',
    attributes: {
        content: {
            type: 'string',
            source: 'html',
            selector: 'p'
        }
    },

    edit: ({ attributes, setAttributes }) => {
        return (
            <RichText
                tagName="p"
                className="text-flow-animation"
                value={attributes.content}
                onChange={(newContent) => setAttributes({ content: newContent })}
                placeholder="ここにテキストを入力…"
            />
        );
    },

    save: ({ attributes }) => {
        return (
            <RichText.Content
                tagName="p"
                className="text-flow-animation"
                value={attributes.content}
            />
        );
    }
});
