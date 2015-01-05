<?php
// Prevent direct access to file
defined('ABSPATH') or die("No script kiddies please!");

class OSDMailChimp {
    private $mcKey;
    private $baseURL;
    private $mailChimpArray;

    function __construct($args = NULL) { 
        $saved_key = get_option('osd_mc_form_options');
        $saved_key = $saved_key['mcKey'];
        $this->mcKey = (isset($args['mcKey'])) ? $args['mcKey'] : $saved_key;
        if($this->mcKey == '') {
            //dont do anything without a mc key
            return 'no key';
        }

        $dataCenter = explode('-', $this->mcKey);
        $dataCenter = (isset($dataCenter[1])) ? $dataCenter[1] : 'us7';
        $this->baseURL = 'https://'.$dataCenter.'.api.mailchimp.com/2.0';
        $this->mailChimpArray = array("apikey" => $this->mcKey);
        $this->args = $args;
    }

    public function validateKey() {
        $mailChimpResponse = $this->apiCall(json_encode($this->mailChimpArray), $this->baseURL.'/helper/ping.json');
        if(isset($mailChimpResponse['msg']) && $mailChimpResponse['msg'] == "Everything's Chimpy!") {
            return true;
        }  
        return false;
    }

    public function getLists() {
        $mailChimpResponse = $this->apiCall(json_encode($this->mailChimpArray), $this->baseURL.'/lists/list.json');
        $listArray = array();
        foreach($mailChimpResponse['data'] as $list) {
            $listArray[] = array('name' => $list['name'], 'id' => $list['id']);
        }
        return $listArray;
    }    

    public function getFields($listID) {
        if($listID == '') {
            return 'error';
        }
        $this->mailChimpArray['id'] = array('listID' => $listID);
        $mailChimpResponse = $this->apiCall(json_encode($this->mailChimpArray), $this->baseURL.'/lists/merge-vars.json');
        $fieldArray = array();
        $index = 0;

        if(!isset($mailChimpResponse['status'])) {
            foreach($mailChimpResponse['data'] as $list) {
                foreach($list['merge_vars'] as $field) {
                    $fieldArray[$index] = array('name' => $field['name'], 'tag' => $field['tag'], 'type' => $field['field_type'], 'required' => $field['req']);
                    if($field['field_type'] == 'radio' || $field['field_type'] == 'dropdown') {
                        $fieldArray[$index]['options'] = $field['choices'];
                    }
                    $index++;
                }
            }

            $fieldArray['formInfo'] = array('listName' => $mailChimpResponse['data'][0]['name'], 'listID' => $mailChimpResponse['data'][0]['id']);
            return $fieldArray;
        }
        
        return 'error';
    }

    public function adminDisplayForm($args) {
        if($args['listID'] == '') {
            return 'error';
        }
        $this->mailChimpArray['id'] = array('listID' => $args['listID']);
        $mailChimpResponse = $this->apiCall(json_encode($this->mailChimpArray), $this->baseURL.'/lists/merge-vars.json');

        if(!isset($mailChimpResponse['status'])) {
            //these fields will not have the placeholder input box
            $noPlcHolder = array('radio', 'dropdown', 'address');

            //create a unique form id
            $formID = (isset($args['formInfo'])) ? $args['formInfo']['formName'] : uniqid();

            $return = "<div class='mcFormWrapper'>
                       <div class='list-name'>".$mailChimpResponse['data'][0]['name']."</div>";
            $return .= (isset($args['formInfo'])) ? "<input name='form[".$formID."][shortCode]' class='shortCode' type='hidden' value='".$formID."' />" : '';
            $return .= "<input type='hidden' name='form[".$formID."][id]' value='".$mailChimpResponse['data'][0]['id']."' />";
            $return .= (isset($args['formInfo'])) ? "<div class='short-code'>Form Shortcode: &nbsp;&nbsp; [osd-mc-form id='".$formID."']</div>" : '';
            $return .= "<div class='field titles'>
                            <div class='name'>Name</div>
                            <div class='tag'>Tag</div>
                            <div class='required'>Required</div>
                            <div class='include'>Include?</div>
                            <div class='placeholder'>Placeholder</div>
                            <div class='class'>Optional Class</div>
                        </div>";
            
            foreach($mailChimpResponse['data'] as $list) {
                foreach($list['merge_vars'] as $field) {
                    $cur_field = ($cur_field = $this->ifset($args['formInfo'][$field['tag']])) ? $cur_field : false;
                    $checked = (($cur_field && isset($cur_field['include'])) || $field['req'] == true) ? " checked='checked'" : '';
                    $placeholder = ($cur_field && isset($cur_field['placeholder'])) ? $cur_field['placeholder'] : '';
                    $class = ($cur_field && isset($cur_field['class'])) ? $cur_field['class'] : '';
                    $disabled = ($field['req'] == true) ? ' disabled' : '';

                    $return .= "<div class='field'>
                                    <div class='name'>".$field['name']."</div>
                                    <div class='tag'>".$field['tag']."</div>";
                    $return .= ($field['req'] == true) ? "<div class='required'>Yes</div>" : "<div class='required'>No</div>";
                    $return .= "<div class='include'>
                                  <input type='checkbox'{$disabled} name='form[".$formID."][".$field['tag']."][include]' value='true'".$checked." />
                                </div>";
                    $return .= "<div class='placeholder'>";
                    $return .= (in_array($field['field_type'], $noPlcHolder)) ? '' : "<input type='text' name='form[".$formID."][".$field['tag']."][placeholder]' value='".$placeholder."' />";
                    $return .= "</div>";
                    $return .= "<div class='class'><input type='text' name='form[".$formID."][".$field['tag']."][class]' value='".$class."' /></div>";
                    $return .= "</div>";
                }
                $return .= "<div class='field'>
                                <div class='success-label'>Custom success message:</div>
                                <div class='success-msg'>
                                    <input type='text' name='form[".$formID."][success-msg]' value='".$this->ifset($args['formInfo']['success-msg'])."' />
                                </div>
                                <div class='msg-class'><input type='text' name='form[".$formID."][msg-class]' value='".$this->ifset($args['formInfo']['msg-class'])."' /></div>
                            </div>";
                $return .= "<div class='field'>
                                <div class='success-label'>Redirect page on success:</div>
                                <div class='success-page'>
                                    ".wp_dropdown_pages(array(
                                        'name' => 'form['.$formID.'][success-page]', 
                                        'selected' => $this->ifset($args['formInfo']['success-page']), 
                                        'show_option_none'=>'None',
                                        'echo' => false
                                        )
                                    )."
                                </div>
                            </div>";
                
                $return .= "<div class='mc-form-remove'>Remove</div></div>";
            }
            
            return $return;
        }

        return 'error';
    }

    public function load_form($args) {
        if(!isset($args['form_id']) || $args['form_id'] == '') {
            return 'error';
        }
        if ($user_fields = get_option($args['form_id'])) {
            $user_fields = json_decode($user_fields, true);
            $mc_fields = $this->getFields($user_fields['id']);

            if ($mc_fields == 'error') {
                return 'error';
            }
        } else {
            return 'error';
        }

        $form_class = (isset($args['class']) && $args['class'] != '') ? " ".$args['class'] : '';
        $submit_text = (isset($args['atts']['submit_text'])) ? $args['atts']['submit_text'] : $_POST['submit_text'];
        $submit_text = ($submit_text != "") ? $submit_text : "Submit";
        $html = "<form class='osd-mc-form{$form_class}'>";
        $html .= "<input type='hidden' name='shortCode' value='{$args['form_id']}' />";
        $html .= "<input type='hidden' name='listID' value='{$mc_fields['formInfo']['listID']}' />";
        $filterable = "";
        $fields_html = "";
        
        foreach ($mc_fields as $key => $field) {
            if ($key === "formInfo" || !isset($user_fields[$field['tag']]['include'])) { 
                continue; 
            }
            $required = ($field['required'] == "1") ? " required" : "";
            $requiredLabel = ($field['required'] == "1") ? "<span class='osd-mc-form-required'>*</span>" : "";
            $field_class = (isset($user_fields[$field['tag']]['class']) && $user_fields[$field['tag']]['class'] != '') ? ' '.$user_fields[$field['tag']]['class'] : '';
            $field_placeholder = (isset($user_fields[$field['tag']]['placeholder']) && $user_fields[$field['tag']]['placeholder'] != '') ? $user_fields[$field['tag']]['placeholder'] : '';
            $field_html = "";

            if ($field['type'] == "radio") {
                $field_html .= "<div class='osd-mc-field-group{$field_class}'>";
                $field_html .= "<label>{$field['name']}{$requiredLabel}</label>";
                foreach ($field['options'] as $key => $option) {
                    $field_html .= "<div class='osd-mc-field'>";
                    $field_html .= "<input type='{$field['type']}' id='{$field['tag']}-{$key}' name='fields[{$field['tag']}]' value='{$option}'{$required} />";
                    $field_html .= "<label for='{$field['tag']}-{$key}'>{$option}</label>";
                    $field_html .= "</div>";
                }
                $field_html .= "</div>";
            } else if ($field['type'] == "dropdown") {
                $field_html .= "<div class='osd-mc-field{$field_class}'>";
                $field_html .= "<label>{$field['name']}{$requiredLabel}</label>";
                $field_html .= "<select name='fields[{$field['tag']}]'{$required}>";
                foreach ($field['options'] as $key => $option) {
                    $field_html .= "<option value='{$option}'>{$option}</option>";
                }
                $field_html .= "</select></div>";
            } else if ($field['type'] == "address") {
                $field_html .= "<div class='osd-mc-field address1{$field_class}'><label>Address{$requiredLabel}</label><input name='fields[osd-mc-address][{$field['tag']}][addr1]' type='text'{$required} /></div>";
                $field_html .= "<div class='osd-mc-field address2{$field_class}'><label>Address 2</label><input name='fields[osd-mc-address][{$field['tag']}][addr2]' type='text'{$required} /></div>";
                $field_html .= "<div class='osd-mc-field city{$field_class}'><label>City{$requiredLabel}</label><input name='fields[osd-mc-address][{$field['tag']}][city]' type='text'{$required} /></div>";
                $field_html .= "<div class='osd-mc-field state{$field_class}'><label>State{$requiredLabel}</label><input name='fields[osd-mc-address][{$field['tag']}][state]' type='text'{$required} /></div>";
                $field_html .= "<div class='osd-mc-field zip{$field_class}'><label>Zip{$requiredLabel}</label><input name='fields[osd-mc-address][{$field['tag']}][zip]' type='text'{$required} /></div>";
                $field_html .= "<div class='osd-mc-field country{$field_class}'><label>Country{$requiredLabel}</label><input name='fields[osd-mc-address][{$field['tag']}][country]' type='text'{$required} /></div>";
            } else if ($field['type'] == "phone") {
                $type = "type='tel' ";
                $pattern = "";
                //$pattern = "pattern='[0-9]{3,3}-[0-9]{3,3}-[0-9]{4,4}' ";
            
                $field_html .= "<div class='osd-mc-field{$field_class}'>";
                $field_html .= "<label for='{$field['tag']}'>{$field['name']}{$requiredLabel}</label>";
                $field_html .= "<input name='fields[osd-mc-phone][{$field['tag']}]' {$type} placeholder='{$field_placeholder}'{$required}{$pattern} />";
                $field_html .= "</div>";
            } else {
                $pattern = "";
                $max_length = "";
                if ($field['type'] == "email") {
                    $type = "type='email' ";
                } else if ($field['type'] == "imageurl" || $field['type'] == "url") {
                    $type = "type='url' ";
                } else if ($field['type'] == "number") {
                    $type = "type='number' ";
                } else {
                    $type = "type='text' ";
                    $max_length = "maxlength='256' ";
                }

                $field_html .= "<div class='osd-mc-field{$field_class}'>";
                $field_html .= "<label for='{$field['tag']}'>{$field['name']}{$requiredLabel}</label>";
                $field_html .= "<input name='fields[{$field['tag']}]' {$type} placeholder='{$field_placeholder}'{$required}{$pattern}{$max_length} />";
                $field_html .= "</div>";
            }
            $fields_html .= $field_html;
        }
        $message_html .= "<div class='osd-mc-message'></div>";
        $submit_html .= "<div class='osd-mc-submit-cont'><input class='osd-mc-submit' type='submit' value='{$submit_text}' /></div>";
        $filterable .= $fields_html . $message_html . $submit_html;
        $html .= apply_filters($args["form_id"], $filterable, $fields_html, $message_html, $submit_html);
        $html .= "</form>";
        return $html;
    }

    public function subscribe($data) {
        if(!isset($data['fields']['EMAIL']) || !isset($data['listID']) || $data['fields']['EMAIL'] == '' || $data['listID'] == '') {
            return 'error: please provide required fields';
        }

        $double_optin_setting = get_option('osd_mc_form_options');
        if($double_optin_setting['optIN'] == 'single') {
            $this->mailChimpArray['double_optin'] = 0;
        }
        $this->mailChimpArray['id'] = $data['listID'];
        $this->mailChimpArray['email'] = array('email' => $data['fields']['EMAIL']);
        $this->mailChimpArray['merge_vars'] = array('optin_ip' => $_SERVER['REMOTE_ADDR'],
                                                    'optin_time' => date("Y-m-d H:i:s"));
        foreach($data['fields'] as $tag => $field) {
            if ($tag == 'osd-mc-address') {
                foreach($field as $address_tag => $address_fields) {
                    $this->mailChimpArray['merge_vars'][$address_tag] = $address_fields;
                }
            } else if ($tag == 'osd-mc-phone') {                
                foreach($field as $phone_tag => $phone_field) {
                    // Future number formatting?
                    // $phone_field = preg_replace('/\D/', '', $phone_field);
                    // if (strlen($phone_field) > 9) {
                    //     $phone_field = substr($phone_field, -10);
                    //     $phone_field = substr_replace($phone_field, '-', -7, -7);
                    //     $phone_field = substr_replace($phone_field, '-', -4, -4);
                    // }
                    $this->mailChimpArray['merge_vars'][$phone_tag] = $phone_field;
                }
            } else {
                $this->mailChimpArray['merge_vars'][$tag] = $field;
            }
        }

        $mailChimpResponse = $this->apiCall(json_encode($this->mailChimpArray), $this->baseURL.'/lists/subscribe.json');
        if(isset($mailChimpResponse['euid'])) {
            $form_options = json_decode(get_option($data['shortCode']), true);
            if (isset($form_options['success-page']) && $form_options['success-page'] != '') {
                return json_encode(array('redirect' => 1, 'url' => get_page_link($form_options['success-page'])));
            } else if (isset($form_options['success-msg']) && $form_options['success-msg'] != '') {
                return $form_options['success-msg'];
            } else {
                return apply_filters('the_content', get_option('osd_mc_form_submission_message'));
            }
        }
        return 'error';
    }

    //checks if a var isset and return a value
    private function ifset(&$var, $else = false) {
        return isset($var) && $var ? $var : $else;
    }

    private function apiCall($data, $url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $curlData = curl_exec($curl);
        curl_close($curl);
       
        return json_decode($curlData, true);
    }
}