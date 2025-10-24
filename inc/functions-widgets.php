<?php

/********************************************************************/
/* Profile表示 s	*/
/********************************************************************/

class Integlight_widget_profile extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'integlight_profile_widget',
            __('[Integlight] Display Profile', 'integlight'),
            array('description' => __('Display the profile of a selected user', 'integlight'))
        );
    }

    public function widget($args, $instance)
    {
        $user_id = !empty($instance['user_id']) ? (int) $instance['user_id'] : 0;
        if ($user_id > 0) {
            $display_name = get_the_author_meta('display_name', $user_id);
            $description = get_the_author_meta('description', $user_id);
            $author_url = get_the_author_meta('user_url', $user_id);

            echo $args['before_widget'];
?>
            <div class="integlight-author-profile-widget">



                <a href="<?php echo esc_url($author_url); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo get_avatar($user_id, 96); ?>
                    <?php echo esc_html($display_name); ?>
                </a>

                <p><?php echo wp_kses_post(nl2br($description)); ?></p>
            </div>
<?php
            echo $args['after_widget'];
        }
    }

    public function form($instance)
    {
        $user_id = !empty($instance['user_id']) ? (int) $instance['user_id'] : 0;
        $users = get_users(array('orderby' => 'display_name'));

        echo '<p><label for="' . $this->get_field_id('user_id') . '">' . __('User to display:', 'integlight') . '</label>';
        echo '<select class="widefat"  id="' . $this->get_field_id('user_id') . '" name="' . $this->get_field_name('user_id') . '">';
        echo '<option value="">' . __('-- Please select a user --', 'integlight') . '</option>';


        foreach ($users as $user) {
            printf(
                '<option value="%d"%s>%s</option>',
                $user->ID,
                selected($user_id, $user->ID, false),
                esc_html($user->display_name)
            );
        }
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['user_id'] = (!empty($new_instance['user_id'])) ? (int) $new_instance['user_id'] : 0;
        return $instance;
    }
}

function integlight_register_widgets()
{
    register_widget('Integlight_widget_profile');
}
add_action('widgets_init', 'integlight_register_widgets');

/********************************************************************/
/* Profile表示 e	*/
/********************************************************************/
