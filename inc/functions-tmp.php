<?php
add_action('after_setup_theme', function () {
    $old = get_theme_mod('integlight_hometype_setting');

    // 旧データを持つユーザーのみ対象
    if ($old === 'home1' || $old === 'home2') {
        switch ($old) {
            case 'home1':
                $new = 'siteType1';
                break;
            case 'home2':
                $new = 'siteType2';
                break;
            default:
                return;
        }

        // 新値を保存
        set_theme_mod('integlight_hometype_setting', $new);
    }
});
