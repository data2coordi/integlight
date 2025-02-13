import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

registerBlockType('integlight/hello-world-block', {
    title: 'Hello World Block',
    icon: 'smiley',
    category: 'widgets',

    edit: () => {
        const blockProps = useBlockProps();
        return <p {...blockProps}>Hello, World1.1!</p>;
    },

    save: () => {
        const blockProps = useBlockProps.save();
        return <p {...blockProps}>Hello, World1.1!</p>;
    }
});
