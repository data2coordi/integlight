import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('integlight/hello-world-block2', {
    title: 'Hello World Block2',
    icon: 'smiley',
    category: 'widgets',

    edit: () => {
        const blockProps = useBlockProps();
        return <p {...blockProps}>Hello, World2.1!</p>;
    },

    save: () => {
        const blockProps = useBlockProps.save();
        return <p {...blockProps}>Hello, World2.1!</p>;
    }
});
