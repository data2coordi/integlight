import { registerBlockType } from '@wordpress/blocks';
import {
    InnerBlocks,
    RichText,
    InspectorControls,
    useBlockProps
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import './editor.css';
import './style.css';

/**
 * 子ブロック「タブ」の登録
 */
registerBlockType('integlight/tab', {
    title: 'タブ',
    parent: ['integlight/tab-block'],
    icon: 'screenoptions',
    category: 'layout',
    attributes: {
        tabTitle: {
            type: 'string',
            source: 'html',
            selector: '.tab-title h4',
            default: '' // デフォルト値を空にする
        }
    },
    edit: (props) => {
        const { attributes: { tabTitle }, setAttributes, className } = props;
        return (
            <div className={`${className} tab`}>
                <div className="tab-title">
                    <RichText
                        tagName="h4"
                        placeholder="タブのタイトル..."
                        value={tabTitle}
                        onChange={(value) => setAttributes({ tabTitle: value })}
                    />
                </div>
                <div className="tab-content">
                    <InnerBlocks />
                </div>
            </div>
        );
    },
    save: (props) => {
        const { attributes: { tabTitle } } = props;
        return (
            <div className="wp-block-integlight-tab tab">
                <div className="tab-title">
                    <RichText.Content tagName="h4" value={tabTitle} />
                </div>
                <div className="tab-content">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});






/**
 * 親ブロック「タブブロック」の登録
 */
registerBlockType('integlight/tab-block', {
    edit: (props) => {


        const contentBlockProps = useBlockProps({
            className: 'tabs-block'
        });


        return (
            <Fragment>
                <InspectorControls>
                    <PanelBody title="タブ設定" initialOpen={true}>
                        {/* ここにインスペクター用の設定項目を追加可能 */}
                    </PanelBody>
                </InspectorControls>
                <div {...contentBlockProps}>
                    <div className="tabs-navigation-editor">
                        <p>※タブの切替はフロントエンドで反映されます。</p>
                    </div>
                    <div className="tabs-content-editor">
                        <InnerBlocks
                            allowedBlocks={['integlight/tab']}
                            template={[['integlight/tab', {}]]}
                            templateLock={false}
                            renderAppender={InnerBlocks.ButtonBlockAppender}
                        />
                    </div>
                </div>
            </Fragment>
        );
    },
    save: () => {
        return (
            <div className="tabs">
                <div className="tabs-content">
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
