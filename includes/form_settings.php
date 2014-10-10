<?php
// Prevent direct access to file
defined('ABSPATH') or die("No script kiddies please!");

//SETTINGS PAGE
$formsPage = new OSDMailChimpForms();

class OSDMailChimpForms {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_submenu_item'));
        add_action('admin_init', array($this, 'page_init'));
    }

    //add options page to wp
    public function add_submenu_item() {
        add_submenu_page(
            'osd-mailchimp-form-options', 
            'OSD MailChimp Forms', 
            'Forms', 
            'manage_options',
            'osd-mailchimp-forms', 
            array($this, 'create_admin_page')
        ); 
    }

    //create options page
    public function create_admin_page() {
        global $wpdb;
        $mailChimp = new OSDMailChimp();
        $prefix = $wpdb->base_prefix;

        // Get saved forms
        $sql = "SELECT * 
                FROM ".$prefix."options 
                WHERE option_name LIKE 'osd_mc_forms_%'";
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        //add styling to the page
        $this->addStyle();
        ?>
        <div class="wrap">
            <h2>OSD MailChimp Forms</h2>   
            <?php
            //display any messages
            if($_GET['settings-updated'] == 'true') { ?>
                <div class="updated settings-error" id="setting-error-settings_updated"> 
                    <p><strong>Settings saved.</strong></p>
                </div>
            <?php } ?>
            <select id="listSelect">
                <option value="">Select</option>
                <?php
                //preload mc lists into select box
                $lists = ($mailChimp->getLists());
                foreach($lists as $list) {
                    echo "<option value='".$list['id']."'>".$list['name']."</option>";
                }
                ?>      
            </select>
            <div class="button button-secondary" id="addForm">Add Form</div>

            <form id="osd-mc-forms">
                <?php
                //add the section  
                do_settings_sections('osd-mailchimp-forms');
                //add the nonces
                wp_nonce_field('save_osd_mc_forms');
                
                //add the saved forms
                foreach($results as $form) {
                    $formInfo = json_decode($form['option_value'], true);
                    $formInfo['formName'] = $form['option_name'];
                    $formHTML = $mailChimp->adminDisplayForm(array('listID' => $formInfo['id'], 'formInfo' => $formInfo));
                    echo $formHTML;
                }
                submit_button(); 
                ?>
                <div class='message'></div>
            </form>
        </div>
        <?php

        //add js to the page
        $this->addJS();
    }

    //register / add options 
    public function page_init() {        
        add_settings_section(
            'mailchimp_forms', // ID
            'My Mailchimp Forms', // Title
            NULL, // Callback
            'osd-mailchimp-forms' // Page
        );   
    }

    public function addJS() {
        ?>
        <script type='text/javascript'>
            jQuery('#addForm').click(function() {
                get_form(jQuery('#listSelect').val());
            });

            jQuery('#submit').click(function(e) {
                e.preventDefault();
                save_forms();
            });

            function get_form(list) {
                if(list == '') {
                    return;
                }

                var data = 'action=osd_mc_load_settings_form&list='+list+'&osd_mc_ajax=true&wp_nonce=<?php echo wp_create_nonce('osd_mc_load_settings_form'); ?>';
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo site_url(); ?>/wp-admin/admin-ajax.php",
                    data: data
                }).done(function(response) {
                    jQuery('p.submit').before(response);
                });
            }

            jQuery('#wpcontent').on('click', '.mc-form-remove', removeForm);
            function removeForm(ev) {
                var formWrap = jQuery(this).parents('.mcFormWrapper');
                var shortCode = jQuery(formWrap).find('input[class=shortCode]').val();
                jQuery(this).html('Removing...');
                
                if(shortCode != 'undefined' && shortCode != '') {
                    document.querySelector('.message').innerHTML = "";

                    var data = "action=delete_mc_form&remove="+shortCode+"&osd_mc_ajax=true&wp_nonce=<?php echo wp_create_nonce('delete_mc_form'); ?>";
                    jQuery.ajax({
                        type: "POST",
                        url: "<?php echo site_url(); ?>/wp-admin/admin-ajax.php",
                        data: data
                    }).done(function(response) {
                        jQuery(formWrap).slideUp("slow", function() {
                            jQuery(formWrap).remove();
                        });
                    });
                }
            }

            function save_forms() {
                var saveForm = jQuery('#osd-mc-forms');
                var disabled = saveForm.find(':input:disabled').removeAttr('disabled');
                var data = saveForm.serialize();
                data += '&osd_mc_ajax=true&action=osd_admin_save_forms&wp_nonce=<?php echo wp_create_nonce('osd_admin_save_forms'); ?>';
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo site_url(); ?>/wp-admin/admin-ajax.php",
                    data: data
                }).done(function(response) {
                    window.location += "&settings-updated=true";
                });
            }
        </script>
        <?php
    }

    public function addStyle() {
        ?>
        <style type="text/css">
			.mcFormWrapper {
                border-top: 1px solid #ccc;
                margin-top: 30px;
                max-width: 50%;
                min-width: 650px;
                padding-top: 20px;
            }
            .mcFormWrapper > .list-name {
                font-weight: 700;
                font-size: 125%;
                margin-bottom: 10px;
            }
            .mcFormWrapper > .short-code {
                background-color: #333;
                color: #eee;
                padding: 5px 10px;
                text-align: right;
            }
			.mcFormWrapper > .field {
				padding: 5px 0;
                border: 1px solid #aaa;
                border-top: none;
			}
			.mcFormWrapper > .field.titles {
                background-color: #0074a2;
                border-top: 1px solid #aaa;
                color: #fff;
                font-size: 115%;
                font-weight: bold;
            }
			.mcFormWrapper > .field {
				background-color: #fff;	
			}
            .mcFormWrapper,
            .mcFormWrapper > .field:after {
                content: "";
                display: block;
                clear: both;
            }
			.mcFormWrapper > .field > div {
				display: inline-block;
				text-align: center;
				width: 16.6%;
                float: left;
			}
            .mcFormWrapper > .field > .required {
                width: 13%;
            }
            .mcFormWrapper > .field > .include {
                width: 13%;
            }
            .mcFormWrapper > .field > .placeholder,
            .mcFormWrapper > .field > .class {
                width: 20%;
                margin-right: .4%;
                min-height: 1px;
            }
            .mcFormWrapper > .field > .placeholder > input,
            .mcFormWrapper > .field > .class > input {
                width: 100%;
            }
            .mcFormWrapper > .field > .success-label {
                width: 26%;
            }
            .mcFormWrapper .field .success-msg {
                width: 53%;
            }
            .mcFormWrapper .field .success-msg input,
            .mcFormWrapper .field .msg-class > input {
                width: 100%;
            }
            .mcFormWrapper > .field .msg-class {
                margin-left: 0.5%;
                width: 20%;
            }
            .mcFormWrapper .mc-form-remove {
                float: right;
                background: #A80000;
                margin: 10px 0 20px 0;
                padding: 5px 10px;
                border-radius: 3px;
                color: white;
                cursor: pointer;
            }
            .mcFormWrapper .mc-form-remove:hover {
                background: #850000;
            }
            .submit {
                clear: both;
            }
        </style>    
        <?php
    }
}