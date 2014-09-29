<?php
function osd_filter_text_widgets($content) {
    return do_shortcode($content);
}
add_filter('widget_text', 'osd_filter_text_widgets');

function osd_replace_shortcode($atts) {
    $userFields = json_decode(get_option($atts['id']), true);
    $html = "<div class='osd-mc-form-wrapper' data-form-id='{$atts['id']}' data-submit-text='{$atts['submit_text']}' data-class='{$atts['class']}'><div class='osd-mc-loading-message'>Loading Form...</div></div>";
    
    // This is for generating the form without AJAX
    // $html = osd_mc_form_load(array(
    //     'nonce' => wp_create_nonce('load_mc_form'), 
    //     'atts' => $atts,
    //     'return' => true));
    // $html = "<div class='osd-mc-form-wrapper' data-form-id='".$atts['id']."' data-submit-text='".$atts['submit_text']."' data-class='".$atts['class']."'>".$html."</div>";
    return $html;
}
add_shortcode('osd-mc-form', 'osd_replace_shortcode');