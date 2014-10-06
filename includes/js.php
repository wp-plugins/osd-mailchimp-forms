<?php
// Prevent direct access to file
defined('ABSPATH') or die("No script kiddies please!");

// Injects the OSD Mail Chimp JavaScript into the footer of non-admin pages
function osd_mail_chimp_js() {
    ?>
    <script async='true'>
        (function() {
            var osd_mc_forms = document.querySelectorAll('.osd-mc-form-wrapper');
            var xhrs = [];
            for (var i=0, l=osd_mc_forms.length; i < l; i++) {
                var id = osd_mc_forms[i].getAttribute('data-form-id');
                var submit_text = osd_mc_forms[i].getAttribute('data-submit-text');
                var classes = osd_mc_forms[i].getAttribute('data-class');

                var xhr = new XMLHttpRequest();
                xhrs.push(xhr);
                var data = "form_id=" + id + "&submit_text=" + submit_text + "&class=" + classes + "&osd_mc_ajax=true&wp_nonce=<?php echo wp_create_nonce('osd_mc_load_form'); ?>" + "&action=osd_mc_load_form";
                var cont = osd_mc_forms[i];
                xhr.open("POST", "<?php echo site_url(); ?>/wp-admin/admin-ajax.php");
                xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                xhr.onreadystatechange = loadFormHtml;
                xhr.send(data);
            }

            // Loads the AJAX response into the form container
            function loadFormHtml() {
                if (this.readyState === 4 && this.status === 200) {
                    var cont = osd_mc_forms[xhrs.indexOf(this)];
                    cont.innerHTML = this.responseText;
                    var submit = cont.querySelector('.osd-mc-submit');
                    if (submit !== null) {
                        submit.onclick = submit_mc_form;
                    }
                }
            }


            // OSD Mailchimp form submission
            function submit_mc_form(ev) {
                ev.preventDefault();
                var form = jQuery(this).parents('form');

                // Client side form validation                    
                if (HTMLFormElement.prototype.checkValidity !== undefined) {
                    var fields = form[0].querySelectorAll('input, select');
                    var valid = true;
                    for (var i=0, l=fields.length; i < l; i++) {
                        if (!fields[i].checkValidity()) {
                            valid = false;
                            fields[i].parentElement.className += " osd-mc-error";
                        } else {
                            fields[i].parentElement.className = fields[i].parentElement.className.replace(" osd-mc-error", "");
                        }
                    }
                    if (valid === false) {
                        form[0].querySelector('.osd-mc-message').innerHTML = "Sorry, there was an error. Please fill out the form with the correct information.";
                        return;
                    }
                }
                
                var submit_button = jQuery(this);
                var curr_sub_txt = jQuery(submit_button).val();
                jQuery(submit_button).val('Processing...');
                var data = jQuery(form).serialize();
                data += "&osd_mc_ajax=true&wp_nonce=<?php echo wp_create_nonce('osd_mc_subscribe'); ?>&action=osd_mc_subscribe"
                var userMsg = jQuery(form).find('.osd-mc-message');
                jQuery.ajax({
                    type: "POST",
                    url: "<?php echo site_url(); ?>/wp-admin/admin-ajax.php",
                    data: data
                }).done(function(response){
                    jQuery(submit_button).val(curr_sub_txt);
                    jQuery(userMsg).html(response);
                    if(response != 'error') {
                        jQuery(form).trigger('reset');
                    }
                    setTimeout(function() {
                        jQuery(userMsg).html('');
                    }, 5000);
                });
            }
        })();
    </script>
    <?php
}
add_action('wp_footer', 'osd_mail_chimp_js');