<?php

/*
 * CUSTOM ELEMENTS actions library
 * (C) 2012 - David Cramer
 */

add_action('init', 'msc_start');

add_action('widgets_init', 'msc_widget_init');

add_action('admin_head-widgets.php', 'msc_widgetcss');
add_action('admin_head-widgets.php', 'msc_widgetscripts');
add_action('admin_footer-widgets.php', 'msc_widgetjs');

add_action('wp_loaded', 'msc_process');
add_action('admin_menu', 'msc_menus');
add_action('wp_footer', 'msc_footer');
add_action('wp_head', 'msc_header');
add_action('get_header', 'msc_getheader', 100);
add_action('wp_footer', 'msc_getfooter');
add_action('media_buttons', 'msc_button', 11);

add_filter('template_include', 'msc_contentPrefilter');


add_shortcode('celement', 'msc_doShortcode');
$Elements = get_option('CE_ELEMENTS');
if (!empty($Elements)) {
    if (phpversion() >= 5.2) {
        foreach ($Elements as $element) {
            if (!empty($element['shortcode']) && !empty($element['state'])) {
                if($element['elementType'] == 1 || $element['elementType'] == 3){
                    add_shortcode($element['shortcode'], 'msc_doShortcode');
                }
            }
        }
    }
}




if(!empty($_GET['action'])){

    $showSaveHelp = get_option('CE_DISMISS_SAVE');
    if(empty($showSaveHelp)){
        add_action('admin_enqueue_scripts', 'enqueue_custom_admin_scripts');

        function enqueue_custom_admin_scripts() {
            wp_enqueue_style('wp-pointer');
            wp_enqueue_script('wp-pointer');
            add_action('admin_print_footer_scripts', 'custom_print_footer_scripts');
        }
        function custom_print_footer_scripts() {
            $pointer_content = '<h3>Save & Apply</h3>';
            $pointer_content .= '<p><strong>Save:</strong><br />Save all changes and close the editor.</p>';
            $pointer_content .= '<p><strong>Apply:</strong><br />Save all changes and reload the preview.</p>';
            $pointer_content .= '<p><strong>Tip:</strong><br />You can use the keyboard shortcut CTRL+S on Windows or CMD+S on Mac to automatically apply changes and reload preview.</p>';
        ?>
        <script type="text/javascript">
        // <![CDATA[
           jQuery(document).ready( function($) {
            $('#saveButton').pointer({
                content: '<?php echo $pointer_content; ?>',
                position: {
                    edge: 'top',
                    align: 'left'
                },
                close: function() {
                    jQuery.post( ajaxurl, {
                        //pointer: 'saveapply',
                        action: 'msc_dismisssavepointer'
                    });
                }
              }).pointer('open');
           });
        // ]]>
        </script>
        <?php
        };
    };
};

if (is_admin() === true) {
    add_action('wp_ajax_delete_element', 'msc_deleteElement');
    add_action('wp_ajax_apply_element', 'msc_applyElement');
    add_action('wp_ajax_load_elements', 'msc_loadElements');
    add_action('wp_ajax_move_element', 'msc_moveElement');
    add_action('wp_ajax_set_tooltips', 'msc_setTooltips');
    add_action('wp_ajax_loadmedia_page', 'msc_loadmedia_page');
    add_action('wp_ajax_msc_load_elementConfig', 'msc_load_elementConfig');
    add_action('wp_ajax_msc_addgroup', 'msc_addgroup');
    add_action('wp_ajax_msc_selectedgetcat', 'msc_selectedgetcat');
    add_action('wp_ajax_msc_setPreviewColor', 'msc_setPreviewColor');
    add_action('wp_ajax_msc_setPreviewBGColor', 'msc_setPreviewBGColor');
    add_action('wp_ajax_msc_dismisssavepointer', 'msc_dismisssavepointer');
    add_action('wp_ajax_upgrade_elements', 'msc_upgradeElements');
    add_action('wp_ajax_msc_alwaysloadaddgroupSet', 'msc_alwaysloadaddgroupSet');
    add_action('admin_head', 'msc_ajax_javascript');
}
add_action('wp_ajax_my_shortcode_ajax', 'msc_shortcode_ajax');
add_action('wp_ajax_nopriv_my_shortcode_ajax', 'msc_shortcode_ajax');
add_action('wp_ajax_element_ajax', 'msc_shortcode_ajax');
add_action('wp_ajax_nopriv_element_ajax', 'msc_shortcode_ajax');
?>