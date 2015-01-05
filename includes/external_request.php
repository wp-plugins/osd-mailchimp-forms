<?php
// Prevent direct access to file
defined('ABSPATH') or die("No script kiddies please!");

//see if we are running an ajax request, if so validate and add callback function
function osd_validate_ajax_request() {
    if(isset($_POST['osd_mc_ajax'])) {
        if(!wp_verify_nonce($_POST['wp_nonce'], $_POST['action'])) {
           die('Invalid');
           exit;
        }

        add_action('wp_ajax_nopriv_'.$_POST['action'], $_POST['action']);
        add_action('wp_ajax_'.$_POST['action'], $_POST['action']);
    }
}
add_action('wp_loaded', 'osd_validate_ajax_request');

function osd_mc_load_settings_form() {
    $omc = new OSDMailChimp();
    echo $omc->adminDisplayForm(array('listID' => $_POST['list']));
    exit;
}

function osd_validate_mc_key() {
    $omc = new OSDMailChimp();
    echo ($omc->validateKey()) ? 'good' : 'error';
    exit;
}

function osd_mc_subscribe() {
    $omc = new OSDMailChimp();
    echo $omc->subscribe($_POST);
    exit;
}

function osd_get_mc_fields() {
    $omc = new OSDMailChimp();
    echo json_encode($omc->getFields($_POST['getFields']));
    exit;
}

function osd_mc_load_form() {
    $omc = new OSDMailChimp();
    echo $omc->load_form($_POST);
    exit;
}

function osd_admin_save_forms() {
    $counter = 1;
    foreach($_POST['form'] as $form) {
        if(isset($form['shortCode'])) {
            $option_name = $form['shortCode'];
            delete_option($option_name);
            unset($form['shortCode']);
            add_option($option_name, json_encode($form), '', 'no');
        } else {
            while(get_option('osd_mc_forms_'.$counter)) {
                $counter++;
            }
            add_option('osd_mc_forms_'.$counter, json_encode($form), '', 'no');
        }
    }
    exit;
}

function delete_mc_form() {
    delete_option($_POST['remove']);
    exit;
}