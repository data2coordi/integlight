import { registerBlockType } from '@wordpress/blocks';
import {
    InnerBlocks,
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import './editor.css';
import './style.css';

registerBlockType('integlight/slider-block', {
    edit: (props) => {
        const blockProps = useBlockProps({
            className: 'blockSliders-block'
        });

        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="スライダー設定" initialOpen={true}>
                        {/* ここにインスペクター用の設定項目を追加可能 */}
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <div className="blockSliders-navigation-editor">
                        <p>※スライドの切替はフロントエンドで反映されます。</p>
                    </div>
                    <div className="blockSliders-content-editor">
                        <InnerBlocks
                            allowedBlocks={['core/group']}
                            template={[['core/group', { className: 'blockSlider' }]]}
                            templateLock={false}
                            renderAppender={InnerBlocks.ButtonBlockAppender}
                        />
                    </div>
                </div>
            </Fragment>
        );
    },
    save: () => {
        const blockProps = useBlockProps.save({ className: 'blockSliders' });

        return (
            <div {...blockProps}>
                <div className="blockSliders-content">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
