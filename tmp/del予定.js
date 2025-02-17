// WordPressのブロックを登録するための関数をインポート
import { registerBlockType } from '@wordpress/blocks';

// ブロックエディタ用のコンポーネントやフックをインポートします。
// 各コンポーネントはエディター内でのUIや機能を実現するために利用されます。
import {
    useBlockProps,            // ブロックに適用するプロパティ（クラス名、スタイルなど）を管理するReactフック
    InspectorControls,       // エディター右側の設定パネルを表示するためのコンポーネント
    InnerBlocks,             // このブロック内に別のブロックを配置できるようにするコンポーネント
    MediaUpload,             // 画像などのメディアファイルをアップロードするためのコンポーネント
    MediaUploadCheck,        // ユーザーがメディアアップロードできるかどうかを確認するコンポーネント
    PanelColorSettings       // カラー設定パネルを提供するコンポーネント（背景色、文字色、オーバーレイ色など）
} from '@wordpress/block-editor';

// WordPressが提供するUIコンポーネントをインポートします。
// これらはエディター内でのボタンやトグルなどのUI要素を構築するために使用されます。
import { PanelBody, ToggleControl, Button } from '@wordpress/components';

// 国際化（i18n：Internationalization）に対応するための関数。
// __('文字列', 'テキストドメイン') の形で使用し、翻訳可能な文字列として扱います。
import { __ } from '@wordpress/i18n';

// ブロックのフロントエンド（表示側）とエディター内で適用するスタイルシートをインポートします。
import './style.css';   // サイト訪問者向けに表示されるスタイル
import './editor.css';  // エディター内でブロック編集時に適用されるスタイル

/*
  block.json に定義されたブロックのメタデータ（attributes, supports, icon, categoryなど）により、
  WordPressはこのブロックの基本情報を自動的に読み込んで登録します。
  そのため、ここではエディター用のロジック（edit関数とsave関数）だけを定義すれば十分です。
*/

// registerBlockType 関数を用いて、ブロックのエディター用のロジックとフロントエンド出力を定義します。
// 第一引数にはブロックの名前（名前空間/ブロック名）を指定します。
// この例では、"integlight/custom-cover" という名前を利用しています。
registerBlockType('integlight/custom-cover', {
    // edit 関数はエディター内でブロックを編集する際の表示内容と動作を定義します。
    // 引数として渡されるオブジェクトには、block.json で定義した属性 (attributes) と、
    // それらを変更するための関数 (setAttributes) が含まれます。
    edit: ({ attributes, setAttributes }) => {
        // block.jsonで定義した属性が自動的に渡されるので、分割代入で各値を取得します。
        const {
            innerWidthArticle,  // 内部コンテンツの幅設定（記事幅にするか全幅にするかのブール値）
            url,                // 背景画像のURL（画像がアップロードされている場合に値が入る）
            id,                 // アップロードされた画像のID
            alt,                // 画像の代替テキスト（アクセシビリティ用）
            focalPoint,         // 画像の焦点位置（オブジェクト。例: {x: 0.5, y: 0.5}）
            dimRatio,           // オーバーレイの透明度（0～100の数値）
            overlayColor,       // オーバーレイの色
            backgroundColor,    // 背景色
            textColor           // 文字色
        } = attributes;

        /*
          useBlockProps フックは、このブロックに適用するプロパティを返します。
          ここではクラス名とインラインスタイルを設定しています。
          - className: ブロックの基本的なスタイルやレイアウトを適用するためのクラスを指定
          - style: 背景色や背景画像、画像の位置を動的に設定するために利用
        */
        const blockProps = useBlockProps({
            className: 'wp-block-integlight-custom-cover alignfull',
            style: {
                backgroundColor: backgroundColor, // 背景色を属性から設定
                // 背景画像のURLが存在する場合のみ、backgroundImageプロパティにURLを設定
                backgroundImage: url ? `url(${url})` : undefined,
                // 画像の焦点位置をパーセンテージに変換して背景画像の表示位置を指定
                backgroundPosition: `${focalPoint.x * 100}% ${focalPoint.y * 100}%`
            }
        });

        // 内部コンテンツの幅を記事幅にするか全幅にするかで、適用するクラス名を切り替えます。
        // これにより、内部コンテンツのスタイル（例えば中央寄せや左右の余白の制御）が変化します。
        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';

        // エディター内に表示される要素を返します。
        // <> と </> はReactのフラグメント記法で、複数の要素をまとめて返すために使用します。
        return (
            <>
                {/* InspectorControls はエディター右側に表示される設定パネルを提供します */}
                <InspectorControls>
                    {/* PanelBody は設定パネル内のセクションを定義するコンポーネント */}
                    <PanelBody title={__('Cover Settings', 'integlight')}>
                        {/* ToggleControl はスイッチ（オン/オフ）UI。ここでは内部コンテンツの幅を切り替えます */}
                        <ToggleControl
                            label={__('Use Article Width for Inner Content', 'integlight')}
                            checked={innerWidthArticle} // 現在の状態を表示
                            onChange={() => setAttributes({ innerWidthArticle: !innerWidthArticle })} // 状態を反転して更新
                        />
                        {/* MediaUploadCheck でユーザーが画像アップロード可能か確認 */}
                        <MediaUploadCheck>
                            {/* MediaUpload は画像をアップロード・選択するためのコンポーネント */}
                            <MediaUpload
                                // ユーザーが画像を選択した際に呼び出される関数
                                onSelect={(media) =>
                                    setAttributes({
                                        url: media.url,   // 画像のURLを属性に設定
                                        id: media.id,     // 画像のIDを属性に設定
                                        alt: media.alt    // 画像の代替テキストを属性に設定
                                    })
                                }
                                allowedTypes={['image']} // 画像のみアップロード可能に設定
                                value={id} // 現在選択されている画像のID
                                // render プロパティで、アップロードボタンの表示をカスタマイズ
                                render={({ open }) => (
                                    <Button onClick={open} isPrimary>
                                        {
                                            // urlが未設定の場合は「Upload Background Image」、
                                            // すでに画像がある場合は「Change Background Image」と表示
                                            !url ? __('Upload Background Image', 'integlight') : __('Change Background Image', 'integlight')
                                        }
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        {/* 画像が既に設定されている場合、削除するボタンを表示 */}
                        {url && (
                            <Button
                                onClick={() => setAttributes({ url: '', id: undefined, alt: '' })}
                                isSecondary
                                style={{ marginTop: '10px' }}
                            >
                                {__('Remove Background Image', 'integlight')}
                            </Button>
                        )}
                    </PanelBody>
                    {/* PanelColorSettings コンポーネントを使って、背景色・文字色・オーバーレイ色の設定パネルを作成 */}
                    <PanelColorSettings
                        title={__('Color Settings', 'integlight')}
                        initialOpen={false} // 初期状態は閉じた状態で表示
                        colorSettings={[
                            {
                                value: backgroundColor, // 現在の背景色
                                onChange: (newColor) => setAttributes({ backgroundColor: newColor }),
                                label: __('Background Color', 'integlight')
                            },
                            {
                                value: textColor, // 現在の文字色
                                onChange: (newColor) => setAttributes({ textColor: newColor }),
                                label: __('Text Color', 'integlight')
                            },
                            {
                                value: overlayColor, // 現在のオーバーレイ色
                                onChange: (newColor) => setAttributes({ overlayColor: newColor }),
                                label: __('Overlay Color', 'integlight')
                            }
                        ]}
                    />
                </InspectorControls>
                {/* 実際にエディター内に表示されるブロックのプレビュー部分 */}
                <div {...blockProps}>
                    {/* 背景画像が設定されている場合、画像上に半透明のオーバーレイ（重ねレイヤー）を表示 */}
                    {url && (
                        <div className="cover-overlay" style={{
                            // オーバーレイの色を設定。属性 overlayColor があればその値を、
                            // なければ透明な黒（rgba(0,0,0,0)）を使用
                            backgroundColor: overlayColor ? overlayColor : 'rgba(0,0,0,0)',
                            // オーバーレイの透明度を、dimRatio を 0〜1 に変換して指定
                            opacity: dimRatio / 100
                        }} />
                    )}
                    {/* 内部コンテンツを配置するコンテナ。innerClass によりスタイルが変わります */}
                    <div className={`inner-container ${innerClass}`}>
                        <InnerBlocks /> {/* ユーザーがエディター内で配置する内部ブロックがここに表示されます */}
                    </div>
                </div>
            </>
        );
    },

    // save 関数は、投稿が保存された際にフロントエンドに出力されるHTMLを定義します。
    // この出力内容は、サイト訪問者に表示される部分となります。
    save: ({ attributes }) => {
        // エディター内で設定された属性を分割代入で取得
        const {
            innerWidthArticle, // 内部コンテンツの幅設定（記事幅 or 全幅）
            url,             // 背景画像のURL
            focalPoint,      // 背景画像の焦点位置（オブジェクト）
            backgroundColor, // 背景色
            overlayColor,    // オーバーレイの色
            dimRatio         // オーバーレイの透明度（0〜100）
        } = attributes;

        // 内部コンテンツの幅に応じたクラス名を設定
        const innerClass = innerWidthArticle ? 'inner-article' : 'inner-full';

        // フロントエンドで出力されるHTML構造を返します。
        // ここで設定されたスタイルやクラス名は、実際にサイト上に表示されるブロックのレイアウトや見た目に反映されます。
        return (
            <div
                className="wp-block-integlight-custom-cover alignfull" // 外側のラッパー。全幅表示を保証するクラスを適用
                style={{
                    backgroundColor: backgroundColor, // 背景色を適用
                    // 背景画像が設定されていれば、その画像URLを利用して背景画像を設定
                    backgroundImage: url ? `url(${url})` : undefined,
                    // 画像の焦点位置をパーセンテージ表示に変換して設定
                    backgroundPosition: `${focalPoint.x * 100}% ${focalPoint.y * 100}%`
                }}
            >
                {/* 背景画像が存在する場合のみ、オーバーレイ用の div を出力 */}
                {url && (
                    <div className="cover-overlay" style={{
                        // オーバーレイの色。設定されていなければ透明な黒
                        backgroundColor: overlayColor ? overlayColor : 'rgba(0,0,0,0)',
                        // オーバーレイの透明度をdimRatioを元に計算して設定
                        opacity: dimRatio / 100
                    }} />
                )}
                {/* 内部ブロックのコンテンツを配置するコンテナ */}
                <div className={`inner-container ${innerClass}`}>
                    {/*
                      InnerBlocks.Content は、エディター内でユーザーが配置した内部ブロックの内容を
                      フロントエンドにレンダリングするためのコンポーネントです。
                    */}
                    <InnerBlocks.Content />
                </div>
            </div>
        );
    }
});
