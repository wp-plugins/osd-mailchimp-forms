<?php
// Prevent direct access to file
defined('ABSPATH') or die("No script kiddies please!");

//SETTINGS PAGE
$settingsPage = new OSDMailChimpSettings();

class OSDMailChimpSettings {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('admin_init', array($this, 'page_init'));
    }

    //add options page to wp
    public function add_menu_item() {
        add_menu_page(
            'OSD MailChimp Form Settings', 
            'OSD MailChimp', 
            'manage_options', 
            'osd-mailchimp-form-options', 
            array($this, 'create_admin_page'), 
            plugins_url('osd-mailchimp-forms/images/icon.png')
        ); 
    }

    //create options page
    public function create_admin_page() {
        //add styling to the page
        $this->addStyle();

        // Set class property
        $this->options = get_option('osd_mc_form_options');
        $this->successMessage = get_option('osd_mc_form_success_message');
        $this->failureMessage = get_option('osd_mc_form_failure_message');
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>OSD MailChimp Global Settings</h2>   
            <?php
            //display any messages
            if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') { ?>
                <div class="updated settings-error" id="setting-error-settings_updated"> 
                    <p><strong>Settings saved.</strong></p>
                </div>
            <?php } ?>        
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields('osd-mailchimp-form-options');   
                do_settings_sections('osd-mailchimp-form-options');
                submit_button(); 
            ?>
            </form>
        </div>
        <?php

        //add js to the page
        $this->addJS();
    }

    //register / add options 
    public function page_init() {        
        register_setting(
            'osd-mailchimp-form-options', // Option group
            'osd_mc_form_options', // Option name
            array($this, 'sanitize') // Sanitize
        );

        register_setting(
            'osd-mailchimp-form-options', // Option group
            'osd_mc_form_success_message', // Option name
            array($this, 'apply_content_save_pre') // Sanitize
        );

        register_setting(
            'osd-mailchimp-form-options', // Option group
            'osd_mc_form_failure_message', // Option name
            array($this, 'apply_content_save_pre') // Sanitize
        );

        add_settings_section(
            'main_settings', // ID
            'Global MailChimp Form Settings', // Title
            array($this, 'print_section_info'), // Callback
            'osd-mailchimp-form-options' // Page
        );  

        add_settings_field(
            'mcKey', // ID
            'Your Mailchimp API Key', // Title 
            array($this, 'mcKey_callback'), // Callback
            'osd-mailchimp-form-options', // Page
            'main_settings' // Section           
        );      

        add_settings_field(
            'optIN', 
            'Single or Double Opt-In', 
            array($this, 'optIN_callback'), 
            'osd-mailchimp-form-options', 
            'main_settings'
        );      

        add_settings_field(
            'success-message', 
            'Global Custom Success Message (can override on individual form options)', 
            array($this, 'success_message_callback'), 
            'osd-mailchimp-form-options', 
            'main_settings'
        );  

        add_settings_field(
            'failure-message', 
            'Global Custom Failure Message (can override on individual form options)', 
            array($this, 'failure_message_callback'), 
            'osd-mailchimp-form-options', 
            'main_settings'
        ); 
    }

    //sanitize  
    public function sanitize($input) {
        //could be used to sanitize the inputs also
        if(isset($input['mcKey']) && $input['mcKey'] != '') {
            $results = new OSDMailChimp(array('mcKey' => $input['mcKey']));
            if($results->validateKey()) {
                return $input;
            }
        }
        
        //if invalid
        $input['mcKey'] = '';
        return $input;
    }

    public function apply_content_save_pre($content) {
        return apply_filters('content_save_pre', $content);
    }

    //section text
    public function print_section_info() {
        //echo 'some info';
    }

    /**** output to admin settings screen ****/
    public function mcKey_callback() {
        $responseHTML = (isset($this->options['mcKey']) && $this->options['mcKey'] != '') ? 'Key Verified' : '';
        printf(
            '<input type="text" id="mcKey" name="osd_mc_form_options[mcKey]" value="%s" />',
            isset($this->options['mcKey']) ? esc_attr($this->options['mcKey']) : ''
        );
        echo '<div class="verify">Verify</div><div class="key verified">'.$responseHTML.'</div>';
        echo '<br />Please enter your MailChimp API Key above. If the key is invalid it will NOT be stored. The API Key allows your WordPress site to communicate with your MailChimp account.
              For more help, visit the MailChimp Support article <a target="_blank" href="http://kb.mailchimp.com/article/where-can-i-find-my-api-key">Where can I find my API Key?</a>';
    }

    public function optIN_callback() {
        $dSelected = ($this->options['optIN'] && $this->options['optIN'] == 'double') ? true : false;
        ?>
        <select id='optIN' name='osd_mc_form_options[optIN]'>
            <option <?php echo (!$dSelected) ? "selected='selected'" : ''; ?> value='single'>Single</option>
            <option <?php echo ($dSelected) ? "selected='selected'" : ''; ?> value='double'>Double</option>
        </select>
        <br />A single opt-in will add the user to your list without any further interaction. A double opt-in will send an email to the user asking them to confirm their subscription.
        <?php
    }

    public function success_message_callback() {
        $success_message_parameters = array(
            'teeny' => false,
            'textarea_rows' => 10,
            'tabindex' => 1,
            'textarea_name' => 'osd_mc_form_success_message',
            'drag_drop_upload' => true
        );
        $content = apply_filters('content_edit_pre', $this->successMessage);
        wp_editor($content, 'success-content', $success_message_parameters);
    }

    public function failure_message_callback() {
        $failure_message_parameters = array(
            'teeny' => false,
            'textarea_rows' => 10,
            'tabindex' => 1,
            'textarea_name' => 'osd_mc_form_failure_message',
            'drag_drop_upload' => true
        );
        $content = apply_filters('content_edit_pre', $this->failureMessage);
        wp_editor($content, 'failure-content', $failure_message_parameters);
    }
    /**** end output to admin settings screen ****/

    private function addJS() {
        ?>
        <script type='text/javascript'>
            document.onready = function() {
                jQuery('.verify').click(function(e) {
                    validateKey(jQuery('#mcKey').val());
                });

                function validateKey(mcKey) {
                    var displayResponse = jQuery('.key.verified');
                    displayResponse.html('');

                    var data = 'action=osd_validate_mc_key&osd_mc_ajax=true&wp_nonce=<?php echo wp_create_nonce('osd_validate_mc_key'); ?>&mcKey='+mcKey;
                    jQuery.ajax({
                        type: "POST",
                        url: "<?php echo site_url(); ?>/wp-admin/admin-ajax.php",
                        data: data
                    }).done(function(response) {
                        if(response == 'good') {
                            displayResponse.html('Key Verified');
                        } else {
                            displayResponse.html('Invalid API Key');    
                        }
                    });
                }
            }
        </script>
        <?php
    }

    private function addStyle() {
        ?>
        <style type="text/css">
            .verify {
                background-color: blue;
                border-radius: 6px;
                color: #fff;
                cursor: pointer;
                display: inline-block;
                margin-left: 10px;
                padding: 3px 7px;
                vertical-align: middle;
            }
            .verify:hover {
                background-color: navy;
            }
            .verified {
                display: none;
                display: inline-block;
                margin-left: 10px;
                color: green;
            }
        </style>    
        <?php
    }
}