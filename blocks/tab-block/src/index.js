import { useState } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import './editor.css';
import './style.css';

registerBlockType('integlight/tab-block', {
    edit: ({ attributes, setAttributes }) => {
        const [activeTab, setActiveTab] = useState(attributes.activeTab || 0);

        return (
            <div className="integlight-tab-block">
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Active Tab"
                            value={activeTab}
                            onChange={(value) => {
                                setActiveTab(Number(value));
                                setAttributes({ activeTab: Number(value) });
                            }}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="tabs">
                    <button className={activeTab === 0 ? 'active' : ''} onClick={() => setActiveTab(0)}>Tab 1</button>
                    <button className={activeTab === 1 ? 'active' : ''} onClick={() => setActiveTab(1)}>Tab 2</button>
                </div>
                <div className="content">
                    {activeTab === 0 && <p>Content for Tab 1</p>}
                    {activeTab === 1 && <p>Content for Tab 2</p>}
                </div>
            </div>
        );
    },
    save: ({ attributes }) => {
        return (
            <div className="integlight-tab-block">
                <div className="tabs">
                    <button className={attributes.activeTab === 0 ? 'active' : ''}>Tab 1</button>
                    <button className={attributes.activeTab === 1 ? 'active' : ''}>Tab 2</button>
                </div>
                <div className="content">
                    {attributes.activeTab === 0 && <p>Content for Tab 1</p>}
                    {attributes.activeTab === 1 && <p>Content for Tab 2</p>}
                </div>
            </div>
        );
    }
});
