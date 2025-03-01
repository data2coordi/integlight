import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { _x } from '@wordpress/i18n';
registerBlockType('integlight/hello-world-block', {
    title: 'Hello World Block',
    icon: 'smiley',
    category: 'widgets',

    edit: () => {
        const blockProps = useBlockProps();

        console.log(_x("Hello", "block description", "integlight"));
        const localeData = wp.i18n.getLocaleData("integlight");
        console.log(Object.keys(localeData));
        //console.log(localeData["block description\u0000Hello"]);
        //console.log(localeData[""]);

        return <p {...blockProps}>
            {__("Hello", "integlight")}
            {__("Tab switching is reflected when the website is displayed.", "integlight")}
            {__("Enter message here.", "integlight")}
        </p>;
    },

    save: () => {
        const blockProps = useBlockProps.save();
        return <p {...blockProps}>{__("Hello", "integlight")}</p>;
    }
});
