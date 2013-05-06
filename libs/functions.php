<?php

/*
 * My Shortcodes function library
 * (C) 2012 - David Cramer
 */

$Element = false;
$footerOutput = '';
$headerscripts = '';
$javascript = array();
$phpincludes = array();
$contentPrefilters = array();
$headerContent = "";
$footerContent = "";
$registeredElements = array();
$cssincludes = array();
$jsincludes = array();
$elementinstances = array();

function msc_process() {

    global $footerOutput, $headerscripts, $javascript;
    
    if(!empty($_FILES['import']) && isset($_POST['_wpnonce'])){
        if(wp_verify_nonce($_POST['_wpnonce'],'cs-import-shortcode')){
            if(empty($_FILES['import']['error'])){
                $cat = msc_importScript($_FILES['import']['tmp_name']);
                wp_safe_redirect('?page=my-shortcodes&cat='.$cat);
                exit;
            }
        }
        wp_safe_redirect('?page=my-shortcodes');
        exit;
    }

    if(!empty($_POST['data']['_pluginName'])){

        if(wp_verify_nonce($_POST['_wpnonce'],'mspro-exoport-set')){
            $proceedToExport = false;
            foreach($_POST['data'] as $check=>$true){
                if(strpos($check, '_toExport') !== false){
                    $proceedToExport = true;
                }
            }
            if($proceedToExport == true){
                msc_exportPlugin($_POST['data'], $_POST['exportType']);
                die;
            }else{                
                wp_redirect('admin.php?page=my-shortcodes&exporterror='.$_POST['data']['_pluginSet'].'&cat='.$_POST['data']['_pluginSet']);
                die;
            }
        }
    }

    if (!empty($_POST['data'])) {
        if(!wp_verify_nonce($_POST['_wpnonce'],'cs-edit-shortcode')){
            return;
        }
        
        $ID = msc_saveElement(stripslashes_deep($_POST['data']));
        $tabid = sanitize_key(strtolower($_POST['data']['_category']));
        wp_safe_redirect('?page=my-shortcodes&cat='.$tabid.'&el='.$ID);
        exit;
    }

    //return;
    if(is_admin ()){
        if(!empty($_GET['action']) && !empty($_GET['element'])){
            if($_GET['action'] == 'activate'){
                $elements = get_option('CE_ELEMENTS');
                if(!empty($elements[$_GET['element']]['state'])){
                    $elements[$_GET['element']]['state'] = 0;
                }else{
                    $elements[$_GET['element']]['state'] = 1;
                }
                $AlwaysLoads = get_option('CE_ALWAYSLOAD');
                if($elements[$_GET['element']]['elementType'] == 4){
                    if($elements[$_GET['element']]['state'] == 1){
                        $cfg = get_option($_GET['element']);
                        $AlwaysLoads[$_GET['element']]['alwaysLoad'] = $cfg['_alwaysLoadPlacement'];
                        update_option('CE_ALWAYSLOAD', $AlwaysLoads);
                    }else{
                        if(!empty($AlwaysLoads[$_GET['element']])){
                            unset($AlwaysLoads[$_GET['element']]);
                            update_option('CE_ALWAYSLOAD', $AlwaysLoads);
                        }
                    }
                }
                update_option('CE_ELEMENTS', $elements);
                $toCat = sanitize_key($elements[$_GET['element']]['category']);
                if(!empty($_GET['from'])){
                    $toCat = '__allactive____';
                }
                wp_redirect('admin.php?page=my-shortcodes&cat='.$toCat);
                die;
            }
            if($_GET['action'] == 'dup'){
                $Element= get_option($_GET['element']);
                $Element['_ID'] = strtoupper(uniqid('EL'));
                $Element['_shortcode'] = $Element['_ID'];
                $Element['_name'] = $Element['_name'].' duplicate';
                $ID = msc_saveElement($Element);
                $tabid = sanitize_key(strtolower($Element['_category']));
                wp_safe_redirect('?page=my-shortcodes&cat='.$tabid.'&el='.$ID);                
                die;
            }
        }
        if(!empty($_GET['myshortcodeproinsert'])){
            if($_GET['myshortcodeproinsert'] == 'insert'){
                include(MYSHORTCODES_PATH.'shortcode.php');
                die;
            }
            if($_GET['myshortcodeproinsert'] == 'preview'){
                //add_action('wp_before_admin_bar_render', 'msc_removeAdminBar');
                //function msc_removeAdminBar(){
                //    global $wp_admin_bar;
                //    $wp_admin_bar = new WP_Admin_Bar_remove();
                //}
                include(MYSHORTCODES_PATH.'/libs/shortcodepreview.php');
                die;
            }
        }
        return;
    }
    
    return;
}
function msc_start() {
    if(!is_admin()){
        if(!empty($_GET['myshortcodeproinsert'])){
            $user = wp_get_current_user();
            if(in_array('administrator', $user->roles)){
                if($_GET['myshortcodeproinsert'] == 'preview'){
                    include(MYSHORTCODES_PATH.'/libs/editorpreview.php');
                    die;
                }
            }
        }        
        msc_elementDetect();
    }else{
        wp_enqueue_style('msc_icons', MYSHORTCODES_URL . 'styles/mscpicons.css');
    }
}
function msc_getUsedShortcodes($content, $return = array(), $internal = true, $preview = false){

    //$regex = get_shortcode_regex();
    $elements = get_option('CE_ELEMENTS');
    if(!empty($elements)){
        foreach($elements as $key=>$el){
            if((empty($el['state']) || ($el['elementType'] != 1 && $el['elementType'] != 3)) && $preview == false){
                unset($elements[$key]);
            }
        }
    }else{
        return;
    }
    $shortcodes = array();
    if(!empty($internal)){
        $shortcodeKey = array();
    }
    foreach($elements as $ID=>$Element){
        if(!empty($Element['shortcode'])){
            $shortcodes[] = $Element['shortcode'];
            if(!empty($internal)){
                $shortcodeKey[$Element['shortcode']] = $ID;
            }
            for($i = 1; $i<=20; $i++){
                $shortcodes[] = $Element['shortcode'].'_'.$i;
                if(!empty($internal)){
                    $shortcodeKey[$Element['shortcode'].'_'.$i] = $ID;
                }
            }
        }else{
            $shortcodes[] = $ID;
            if(!empty($internal)){
                $shortcodeKey[$ID] = $ID;
            }
        }
    }
    $tagregexp = join( '|', array_map('preg_quote', $shortcodes) );

    // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
    $regex =
              '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '\\b'                              // Word boundary
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]


    //vardump($regex);
    preg_match_all('/' . $regex . '/s', $content, $found);
    if(!empty($internal)){        
        if(!empty($found[2][0])){
            foreach($found[2] as $elkey=>$el){                
                $found[7][$elkey] = $shortcodeKey[$found[2][$elkey]];
            }
        }
    }
    foreach($found[5] as $innerContent){
        if(!empty($innerContent)){
           $new = msc_getUsedShortcodes($innerContent, $found, $internal);
            if(!empty($new)){
                foreach($new as $key=>$val){
                    $found[$key] = array_merge($found[$key], $val);
                }
            }
        }
    }
    
    if(!empty($found[7])){
        foreach($found[7] as $ID){
            $ElementCFG = get_option($ID);
            if(!empty($ElementCFG['_mainCode'])){
               $new = msc_getUsedShortcodes($ElementCFG['_mainCode'], $found, $internal);
                if(!empty($new)){
                    foreach($new as $key=>$val){
                        $found[$key] = array_merge($found[$key], $val);
                    }
                }
            }
        }
    }

    return $found;
}
function msc_elementDetect(){
    
    global $contentPrefilters, $footerOutput, $headerscripts, $phpincludes, $headerContent, $footerContent;

    $elements = get_option('CE_ELEMENTS');
    if(empty($elements)){
        return $template;
    }
    //return;

    $postID = url_to_postid($_SERVER['REQUEST_URI']);
    if(empty($postID)){
        $frontPage = get_option('page_on_front');
        if(!empty($frontPage)){
            $frontPage = get_option('page_on_front');
            $posts[] = get_post($frontPage);
        }else{
            $args = array(
                'numberposts' => get_option('posts_per_page')
            );
            $posts = get_posts($args);
        }
    }else{        
        $posts[] = get_post($postID);
    }
//    vardump($posts);

    // get always loads
    $AlwaysLoads = get_option('CE_ALWAYSLOAD');    
    if(!empty($AlwaysLoads)){
        foreach($AlwaysLoads as $ID=>$Location){
            if(!empty($elements[$ID]['state'])){
                $options = $elements[$ID];                
                if(!empty($options['variables'])){
                    $setAtts = get_option($ID.'_cfg');
                    if(empty($setAtts)){
                        $setAtts = array();
                    }
                    $atts = array();
                    foreach($options['variables']['names'] as $varkey=>$variable){
                        if($options['variables']['type'][$varkey] == 'Dropdown'){
                            $options['variables']['defaults'][$varkey] = trim(strtok($options['variables']['defaults'][$varkey], ','));
                        }
                        if(!empty($options['variables']['multiple'][$varkey])){
                            $endLoop = true;
                            $loopIndex = 1;
                            while($endLoop){
                                if(isset($setAtts[$variable.'_'.$loopIndex])){
                                    $atts[$variable.'_'.$loopIndex] = $setAtts[$variable.'_'.$loopIndex];
                                    $loopIndex++;
                                }else{
                                    if($loopIndex === 1){
                                        $atts[$variable.'_'.$loopIndex] = $options['variables']['defaults'][$varkey];
                                    }
                                    $endLoop = false;
                                }
                            }
                        }else{
                            $atts[$variable] = $options['variables']['defaults'][$varkey];
                        }
                    }
                }
                if(!empty($setAtts)){
                    $shortcodes[$ID][] = shortcode_atts($atts, $setAtts);
                }else{
                    $shortcodes[$ID][] = false;
                }
            }
        }
    }    
    foreach($elements as $key=>$el){
        if(empty($el['state'])){
            unset($elements[$key]);
        }
    }
    // scan the posts
    if(!empty($posts)) {
        foreach($posts as $postKey=>$post){
            $content = $post->post_content;
            $used = msc_getUsedShortcodes($content);
            
            foreach ($elements as $element => $options){
                if(!empty($options['shortcode'])){
                    if ($keys = array_keys($used[2], $options['shortcode'])) {                        
                        foreach($keys as $key){
                            if(!empty($used[3][$key])){
                                $setAtts = shortcode_parse_atts($used[3][$key]);
                            }else{
                                $setAtts = array();
                            }
                            if(!empty($options['variables'])){
                                $atts = array();
                                foreach($options['variables']['names'] as $varkey=>$variable){
                                    if($options['variables']['type'][$varkey] == 'Dropdown'){
                                        $options['variables']['defaults'][$varkey] = trim(strtok($options['variables']['defaults'][$varkey], ','));
                                    }
                                    if(!empty($options['variables']['multiple'][$varkey])){
                                        $endLoop = true;
                                        $loopIndex = 1;
                                        while($endLoop){
                                            if(isset($setAtts[$variable.'_'.$loopIndex])){
                                                $atts[$variable.'_'.$loopIndex] = $setAtts[$variable.'_'.$loopIndex];
                                                $loopIndex++;
                                            }else{
                                                if($loopIndex === 1){
                                                    $atts[$variable.'_'.$loopIndex] = $options['variables']['defaults'][$varkey];
                                                }
                                                $endLoop = false;
                                            }
                                        }
                                    }else{
                                        $atts[$variable] = $options['variables']['defaults'][$varkey];
                                    }
                                }
                            }
                            if(!empty($options['removelinebreaks'])){
                                $preContent = $used[5][$key];
                                for($i=count($used[0])-1; $i>$key; $i--){
                                    $preContent = str_replace($used[0][$i], '[*'.$i.'*]', $preContent);
                                }
                                $preContent = str_replace(array("\r\n", "\r"), "", $preContent);
                                for($i=count($used[0])-1; $i>$key; $i--){
                                    $preContent = str_replace('[*'.$i.'*]', $used[0][$i], $preContent);
                                }
                                $post->post_content = trim(str_replace($used[5][$key], $preContent, $post->post_content));
                                $contentPrefilters[$post->ID] = $post->post_content;
                            }
                            if(!empty($setAtts)){
                                $shortcodes[$element][] = shortcode_atts($atts, $setAtts);
                            }else{
                                $shortcodes[$element][] = false;
                            }
                        }
                    }
                }
            }
        }
    }

    /* Scan for active widgets*/
    $texts = get_option('widget_ceelements');
    $sidebars = get_option('sidebars_widgets'); 
    unset($sidebars['wp_inactive_widgets']);
    foreach ($sidebars as $sidebar => $set) {
        if (is_active_sidebar($sidebar)) {
            foreach ($set as $widget) {
                if (substr($widget, 0, 11) == 'ceelements-') {
                    $id = substr($widget, 11);
                    if (!empty($texts[$id])) {
                        $element = $texts[$id]['_element'];                      
                        if(!empty($elements[$element])){
                            $options = $elements[$element];
                            unset($texts[$id]['_catagory']);
                            unset($texts[$id]['_element']);
                            $instance = $texts[$id];
                            if (!empty($options['shortcode']) && !empty($options['state']) && ($options['elementType'] == 2 || $options['elementType'] == 3)) {
                                if (!empty($instance)) {
                                    $setAtts = $instance;
                                } else {
                                    $setAtts = array();
                                }
                                $atts = array();
                                if (!empty($options['variables'])) {                                    
                                    foreach ($options['variables']['names'] as $varkey => $variable) {
                                        if ($options['variables']['type'][$varkey] == 'Dropdown') {
                                            $options['variables']['defaults'][$varkey] = trim(strtok($options['variables']['defaults'][$varkey], ','));
                                        }
                                        if (!empty($options['variables']['multiple'][$varkey])) {
                                            $endLoop = true;
                                            $loopIndex = 1;
                                            while ($endLoop) {
                                                if (isset($setAtts[$variable . '_' . $loopIndex])) {
                                                    $atts[$variable . '_' . $loopIndex] = $setAtts[$variable . '_' . $loopIndex];
                                                    $loopIndex++;
                                                } else {
                                                    if ($loopIndex === 1) {
                                                        $atts[$variable . '_' . $loopIndex] = $options['variables']['defaults'][$varkey];
                                                    }
                                                    $endLoop = false;
                                                }
                                            }
                                        } else {
                                            $atts[$variable] = $options['variables']['defaults'][$varkey];
                                        }
                                    }
                                }
                                if (!empty($setAtts)) {
                                    $shortcodes[$element][] = shortcode_atts($atts, $setAtts);
                                } else {
                                    $shortcodes[$element][] = false;
                                }
                            }
                        }
                    }
                }
            }
        }
    }    
    if(empty($shortcodes)){
        return;
    }
    //dump($shortcodes);
    foreach ($shortcodes as $ID=>$Instances) {
        foreach($Instances as $no=>$atts){            
            $atts = msc_getDefaultAtts($ID, $atts);            
            msc_processHeaders($ID, $atts);
        }
    }
    
return;
}
function msc_checkInstanceID($id, $process){
    global $elementinstances;
    $elementinstances[$id][$process][] = true;
    $count = count($elementinstances[$id][$process]);
    if($count > 1){
        return $id.($count-1);
    }
    return $id;
}
      
function msc_processHeaders($ID, $atts){
    global $headerscripts, $phpincludes, $wp_scripts, $cssincludes, $elementinstances, $footerContent, $footerOutput, $headerContent, $jsincludes;
       
    $syslibs = array('scriptaculous-root','scriptaculous-builder','scriptaculous-dragdrop','scriptaculous-effects','scriptaculous-slider','scriptaculous-sound','scriptaculous-controls','scriptaculous','swfobject','swfupload','swfupload-degrade','swfupload-queue','swfupload-handlers','jquery','jquery-form','jquery-color','jquery-ui-core','jquery-ui-widget','jquery-ui-mouse','jquery-ui-accordion','jquery-ui-autocomplete','jquery-ui-slider','jquery-ui-tabs','jquery-ui-sortable','jquery-ui-draggable','jquery-ui-droppable','jquery-ui-selectable','jquery-ui-datepicker','jquery-ui-resizable','jquery-ui-dialog','jquery-ui-button','schedule','suggest','thickbox','jquery-hotkeys','sack','quicktags','farbtastic','colorpicker','tiny_mce','prototype','autosave','common','editor','editor-functions','ajaxcat','password-strength-meter','xfn','upload','postbox','slug','post','page','link','comment','comment-repy','media-upload','word-count','theme-preview');
    if(!empty($atts['vararray'])){
        $varArray = $atts['vararray'];
    }
    
    $atts = $atts['atts'];
    
    
    
    $content = '';
    if(isset($atts['_content'])){
        $content = $atts['_content'];
        unset($atts['_content']);
    }
    $element = get_option($ID);
    if(!empty($element)){
        //$instanceID = msc_checkInstanceID('CE'.strtoupper(md5(serialize($atts)).$ID), 'header');
        $instanceID = msc_checkInstanceID('ce'.$element['_shortcode'], 'header');

        $element['_cssCode'] = str_replace('{{_id_}}',$instanceID, $element['_cssCode']);
        if(!empty($element['_javascriptCode'])){
            $element['_javascriptCode'] = str_replace('{{_id_}}',$instanceID, $element['_javascriptCode']);
            $element['_javascriptCode'] = str_replace('{{content}}',$content, $element['_javascriptCode']);
        }
        
        if(!empty($AlwaysLoads[$ID]) && $element['_alwaysLoadPlacement'] > 1){

            if(!empty($element['_mainCode'])){
                $element['_mainCode'] = str_replace('{{content}}', $content, $element['_mainCode']);
                $element['_mainCode'] = str_replace('{{_id_}}',$instanceID, $element['_mainCode']);
            }

        }
        
        
        // Make a list of dependencies for the javascriptcode
        if (!empty($element['_jsLib'])) {

            //if(!wp_script_is('jquery', $list = 'queue' )){
            //    wp_enqueue_script('jquery');
            //}
            
            foreach ($element['_jsLib'] as $handle => $src) {
                $in_footer = false;
                if ($element['_jsLibLoc'][$handle] == 2) {
                    $in_footer = true;
                }
                if(in_array(strtolower(str_replace(' ','-',$src)), $syslibs)){
                    wp_enqueue_script(strtolower(str_replace(' ','-',$src)));
                }else{
                    wp_enqueue_script($handle, $src, false, false, $in_footer);
                }
            }
        }
        if (!empty($element['_cssLib'])) {
            foreach ($element['_cssLib'] as $handle => $src) {
                wp_enqueue_style($handle, $src);
            }
        }

        if(!empty($element['_javascriptCode'])){
            
            $pattern = '\[(\[?)(once)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            preg_match_all('/' . $pattern . '/s', $element['_javascriptCode'], $once);            
            if (!empty($once)) {
                foreach ($once[0] as $onceKey => $onceCode) {
                    if (!empty($once[5][$onceKey])) {
                        //$codeSignature = md5(trim($once[5][$onceKey]));
                        $codeSignature = md5(trim($element['_ID'].'-'.$onceKey));
                        if(empty($jsincludes[$codeSignature])){
                            $element['_javascriptCode'] = str_replace($once[0][$onceKey], trim($once[5][$onceKey]), $element['_javascriptCode']);
                            $jsincludes[$codeSignature] = true;
                            
                        }else{
                            $element['_javascriptCode'] = str_replace($once[0][$onceKey], '', $element['_javascriptCode']);
                        }
                    }
                }
            }
            

            // Simple [if var] replace // really hackish buyt hey :)
            $pattern = '\[(\[?)(if)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            preg_match_all('/' . $pattern . '/s', $element['_javascriptCode'], $jifs);
            if(!empty($jifs[5]) && !empty($element['_variable'])){
                foreach($element['_variable'] as $varID=>$varKey){
                    $pattern = '\[(\[?)(if '.$varKey.')\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                    preg_match_all('/' . $pattern . '/s', $element['_javascriptCode'], $subs);
                    if(!empty($subs[3])){
                        foreach($subs[3] as $ifVal){
                            $element['_javascriptCode'] = str_replace('[if '.$varKey.$ifVal.']', "<?php if('{{".$varKey."}}' === '".trim($ifVal, '=')."'){ ?>", $element['_javascriptCode']);
                        }
                    }

                    $element['_javascriptCode'] = str_replace('[if '.$varKey.']', "<?php if('{{".trim($varKey)."}}' != ''){ ?>", $element['_javascriptCode']);
                    $element['_javascriptCode'] = str_replace('[else]', "<?php }else{ ?>", $element['_javascriptCode']);
                    $element['_javascriptCode'] = str_replace('[/if]', "<?php } ?>", $element['_javascriptCode']);
                }        
            }

            

            $pattern = '\[(\[?)(loop)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            preg_match_all('/' . $pattern . '/s', $element['_javascriptCode'], $loops);
            if (!empty($loops)) {
                foreach ($loops[0] as $loopKey => $loopcode) {                    
                    if (!empty($loops[3][$loopKey]) && !empty($loops[5][$loopKey])) {
                        $LoopCodes[$loopKey] = $loops[5][$loopKey];
                        $element['_javascriptCode'] = str_replace($loopcode, '{{__loop_' . $loopKey . '_}}', $element['_javascriptCode']);
                    }
                }
            } 
            if (!empty($element['_variable'])) {
                foreach ($element['_variable'] as $VarKey => $Variable) {
                    $VarVal = $element['_variableDefault'][$VarKey];
                    if (isset($atts[$Variable])) {
                        $VarVal = $atts[$Variable];
                    }                    
                    $element['_javascriptCode'] = str_replace('{{' . $Variable . '}}', $VarVal, $element['_javascriptCode']);
                }        
                if (!empty($LoopCodes) && !empty($varArray)) {
                    
                    foreach ($LoopCodes as $loopKey => $loopCode) {
                        $loopReplace = '';
                        if (!empty($varArray[trim($loops[3][$loopKey])])) {
                            foreach ($varArray[trim($loops[3][$loopKey])] as $replaceKey => $replaceVar) {
                                $loopReplace .= $loopCode;
                                foreach ($varArray as $Variable => $VarableArray) {
                                    if (!empty($varArray[$Variable][$replaceKey])) {
                                        $loopReplace = str_replace('{{' . $Variable . '}}', $varArray[$Variable][$replaceKey], $loopReplace);
                                    } else {
                                        $loopReplace = str_replace('{{' . $Variable . '}}', '', $loopReplace);
                                    }
                                    $loopReplace = str_replace('[increment]', $replaceKey, $loopReplace);
                                }
                            }
                            $element['_javascriptCode'] = str_replace('{{__loop_' . $loopKey . '_}}', $loopReplace, $element['_javascriptCode']);
                        }
                    }
                }
            }
            
            
        }
        
        
        if (!empty($element['_cssCode'])) {
            $pattern = '\[(\[?)(loop)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            preg_match_all('/' . $pattern . '/s', $element['_cssCode'], $loops);
            if (!empty($loops)) {
                foreach ($loops[0] as $loopKey => $loopcode) {                    
                    if (!empty($loops[3][$loopKey]) && !empty($loops[5][$loopKey])) {
                        $LoopCodes[$loopKey] = $loops[5][$loopKey];
                        $element['_cssCode'] = str_replace($loopcode, '{{__loop_' . $loopKey . '_}}', $element['_cssCode']);
                    }
                }
            } 
            if (!empty($element['_variable'])) {
                foreach ($element['_variable'] as $VarKey => $Variable) {
                    $VarVal = $element['_variableDefault'][$VarKey];
                    if (isset($atts[$Variable])) {
                        $VarVal = $atts[$Variable];
                    }
                    
                    // Simple [if var] replace // really hackish but hey :)
                    // Will make a proper engine for this later. its still exp.
                    $pattern = '\[(\[?)(if)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                    preg_match_all('/' . $pattern . '/s', $element['_cssCode'], $cifs);
                    if(!empty($cifs[5]) && !empty($element['_variable'])){
                        foreach($element['_variable'] as $varID=>$varKey){
                            $pattern = '\[(\[?)(if '.$varKey.')\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                            preg_match_all('/' . $pattern . '/s', $element['_mainCode'], $subs);
                            if(!empty($subs[3])){
                                foreach($subs[3] as $ifVal){
                                    $element['_mainCode'] = str_replace('[if '.$varKey.$ifVal.']', "<?php if('{{".trim($varKey)."}}' != '".trim($ifVal, '=')."'){ ?>", $element['_mainCode']);
                                }
                            }

                            $element['_mainCode'] = str_replace('[if '.$varKey.']', "<?php if('{{".trim($varKey)."}}' != ''){ ?>", $element['_mainCode']);
                            $element['_mainCode'] = str_replace('[else]', "<?php }else{ ?>", $element['_mainCode']);
                            $element['_mainCode'] = str_replace('[/if]', "<?php } ?>", $element['_mainCode']);
                        }        
                    }
                    
                    $element['_cssCode'] = str_replace('{{' . $Variable . '}}', $VarVal, $element['_cssCode']);
                }        
                
                if (!empty($LoopCodes) && !empty($varArray)) {
                    foreach ($LoopCodes as $loopKey => $loopCode) {
                        $loopReplace = '';
                        if (!empty($varArray[trim($loops[3][$loopKey])])) {
                            foreach ($varArray[trim($loops[3][$loopKey])] as $replaceKey => $replaceVar) {
                                $loopReplace .= $loopCode;                                
                                foreach ($varArray as $Variable => $VarableArray) {
                                    if (!empty($varArray[$Variable][$replaceKey])) {
                                        $loopReplace = str_replace('{{' . $Variable . '}}', $varArray[$Variable][$replaceKey], $loopReplace);
                                    } else {
                                        $loopReplace = str_replace('{{' . $Variable . '}}', '', $loopReplace);
                                    }
                                    $loopReplace = str_replace('[increment]', $replaceKey, $loopReplace);
                                }
                            }
                            $element['_cssCode'] = str_replace('{{__loop_' . $loopKey . '_}}', $loopReplace, $element['_cssCode']);
                        }
                    }
                }
            }            
            
            
            $pattern = '\[(\[?)(once)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            preg_match_all('/' . $pattern . '/s', $element['_cssCode'], $once);            
            if (!empty($once)) {
                foreach ($once[0] as $onceKey => $onceCode) {
                    if (!empty($once[5][$onceKey])) {
                        $codeSignature = md5(trim($once[5][$onceKey]));
                        if(empty($cssincludes[$codeSignature])){
                            $element['_cssCode'] = str_replace($once[0][$onceKey], trim($once[5][$onceKey]), $element['_cssCode']);
                            $cssincludes[$codeSignature] = true;
                        }else{
                            $element['_cssCode'] = str_replace($once[0][$onceKey], '', $element['_cssCode']);
                        }
                    }
                }
            }
            
            
            
            ob_start();
                eval(' ?>' . $element['_cssCode'] . ' <?php ');
            $Css = ob_get_clean();
            //dump($atts);
            // A switch would be nice for thouse who want headerbased.
            //$headerscripts .= "\n".$Css;
            
            
            // build a temp file for this.
            $cssCache = get_option('_msc_css_cache');
            $cachePath = wp_upload_dir();
            $cssHash = md5($Css);
            if(!file_exists($cachePath['basedir'].'/cache')){
                mkdir($cachePath['basedir'].'/cache');
            }
            $tempfile = $cachePath['basedir'].'/cache/'.$cssHash.'.css';
            if(empty($cssCache)){
                $cssCache = array();
            }else{
                // cleanout expired stuff
                foreach($cssCache as $cachefile=>$expire){
                    if(mktime()-$expire > 0){
                        unlink($cachePath['basedir'].'/cache/'.$cachefile.'.css');
                        unset($cssCache[$cachefile]);
                    }
                }
            }
            $cached = true;
            if(!file_exists($tempfile)){
                $cached = false;
                $fp = @fopen($tempfile, 'w+');
                if($fp){
                    $cached = true;
                    fwrite($fp, $Css);
                    fclose($fp);
                    $cssCache[$cssHash] = strtotime('+5 days');
                    update_option('_msc_css_cache', $cssCache);
                }
            }            
            if($cached){
                wp_enqueue_style($cssHash, $cachePath['baseurl'].'/cache/'.$cssHash.'.css');    
            }else{
                // if the cache didnt work (permissions or whatever) inline baby!
                $headerscripts .= "\n".$Css;
            }            
            
            
            
            
            
        }
        if (!empty($element['_phpCode'])) {
            if(empty($phpincludes[$element['_ID']])){
                eval($element['_phpCode']);
                $phpincludes[$element['_ID']] = true;
            }
        }



            
            if (!empty($element['_variable'])) {
                foreach ($element['_variable'] as $VarKey => $Variable) {
                    if(!empty($atts[$Variable])){
                    $VarVal = $atts[$Variable];
                    if (!empty($atts[$Variable . '_1'])) {
                        $startcounter = true;
                        $index = 1;
                        while ($startcounter == true) {
                            if (!empty($atts[$Variable . '_' . $index])) {
                                $varArray[trim($Variable)][] = $atts[$Variable . '_' . $index];
                            } else {
                                $startcounter = false;
                            }
                            $index++;
                        }
                    }
                    $element['_javascriptCode'] = str_replace('{{' . $Variable . '}}', $VarVal, $element['_javascriptCode']);
                    }
                }
            }
            ob_start();
            eval(' ?> '.$element['_javascriptCode'].' <?php ');

            $jsout = ob_get_clean();
            
            // build a temp file for javascript.
            $jsCache = get_option('_msc_js_cache');
            $cachePath = wp_upload_dir();
            $jsHash = md5($jsout);
            if(!file_exists($cachePath['basedir'].'/cache')){
                mkdir($cachePath['basedir'].'/cache');
            }
            $tempfile = $cachePath['basedir'].'/cache/'.$jsHash.'.js';
            if(empty($jsCache)){
                $jsCache = array();
            }else{
                // cleanout expired stuff
                foreach($jsCache as $cachefile=>$expire){
                    if(mktime()-$expire > 0){
                        unlink($cachePath['basedir'].'/cache/'.$cachefile.'.js');
                        unset($jsCache[$cachefile]);
                    }
                }
            }
            $cached = true;
            if(!file_exists($tempfile)){
                $cached = false;
                $fp = @fopen($tempfile, 'w+');
                if($fp){
                    $cached = true;
                    fwrite($fp, $jsout);
                    fclose($fp);
                    $jsCache[$jsHash] = strtotime('+5 days');
                    update_option('_msc_js_cache', $jsCache);
                }
            }            
            if($cached){
                wp_enqueue_script($jsHash, $cachePath['baseurl'].'/cache/'.$jsHash.'.js',false,false,true);
            }else{
                // if the cache didnt work (permissions or whatever) inline baby!
                $footerOutput .= "\n".$jsout;
            }            
            
                        


        //};
    }

}
function msc_contentPrefilter($template){
    global $wp_query,$contentPrefilters, $footerOutput, $headerscripts, $phpincludes;
    if(!empty($contentPrefilters)){
        
        foreach($contentPrefilters as $postID=>$newContent){
            //vardump($newContent);
            foreach($wp_query->posts as $postKey=>$post){
                if($postID == $post->ID){
                   $wp_query->posts[$postKey]->post_content = trim($newContent);
                    //vardump($wp_query->posts[$postKey]->post_content, false);
                    //vardump($newContent);
                }
            }
        }
    }

    // Custom do shortcode - disabled for now :(
    return $template;
    $Elements = get_option('CE_ELEMENTS');    
    foreach($Elements as $key=>$el){
        if(empty($el['state'])){
            unset($Elements[$key]);
        }
    }
    if(empty($Elements)){
        return $template;
    }
    foreach($wp_query->posts as $postKey=>$post){
        $Used = msc_getUsedShortcodes($post->post_content, array(), true);        
        foreach($Used[2] as $key=>$shortcode){
            $options = $Elements[$Used[7][$key]];            
            if(!empty($Used[3][$key])){
                $setAtts = shortcode_parse_atts($Used[3][$key]);
            }else{
                $setAtts = array();
            }
            
            if(!empty($options['variables'])){
                $atts = array();
                foreach($options['variables']['names'] as $varkey=>$variable){
                    if($options['variables']['type'][$varkey] == 'Dropdown'){
                        $options['variables']['defaults'][$varkey] = trim(strtok($options['variables']['defaults'][$varkey], ','));
                    }
                    if(!empty($options['variables']['multiple'][$varkey])){
                        $endLoop = true;
                        $loopIndex = 1;
                        while($endLoop){
                            if(isset($setAtts[$variable.'_'.$loopIndex])){
                                $atts[$variable.'_'.$loopIndex] = $setAtts[$variable.'_'.$loopIndex];
                                $loopIndex++;
                            }else{
                                if($loopIndex === 1){
                                    $atts[$variable.'_'.$loopIndex] = $options['variables']['defaults'][$varkey];
                                }
                                $endLoop = false;
                            }
                        }
                    }else{
                        $atts[$variable] = $options['variables']['defaults'][$varkey];
                    }
                }
            }
            if(!empty($setAtts)){
                $Atts = shortcode_atts($atts, $setAtts);
            }else{
                $Atts = false;
            }
            //need to replace the shortcode found with the new code below!!
            $newContent = trim(msc_doShortcode($Atts, $Used[5][$key], $options['shortcode']));

            $post->post_content = str_replace($Used[0][$key], $newContent, $post->post_content);
            //vardump($Used[5][$key], false);
            //vardump(msc_doShortcode($Atts, $post->post_content, $options['shortcode']));
        }

    }
    //die;
    return $template;
}
function msc_shortcode_ajax(){

    if((empty($_POST['process']) && $_POST['function']) && (empty($_POST['shortcode']) && empty($_POST['element']))){
        return false;
    }

    
    
    $elements = get_option('CE_ELEMENTS');
    $declaired = array();
    if(isset($_POST['element'])){
        if(is_array($_POST['element'])){
            foreach($_POST['element'] as $inelement){
                $declaired[$inelement] = 1;
            }
        }else{
            echo $declaired[$_POST['element']] = 1;
        }
    }
    foreach($elements as $ID=>$element){
        if(is_array($_POST['shortcode'])){
            foreach($_POST['shortcode'] as $shortcode){
                if($element['shortcode'] == $shortcode){
                    $declaired[$ID] = 1;
                }
            }
        }else{
            if($element['shortcode'] == $_POST['shortcode']){
                $declaired[$ID] = 1;
            }
        }
    }
    $phpCode = "";
    foreach($declaired as $element=>$inc){
        $Config = get_option($element);
        if(!empty($Config['_phpCode'])){
            $phpCode .= $Config['_phpCode']."\n";
        }
    }
    
    if(!empty($phpCode)){
        eval($phpCode);
        if(isset($_POST['process'])){
            $_POST['process']();
        }
        if(isset($_POST['function'])){
            $_POST['function']();
        }
        
    }
die;
}
function msc_button() {

    echo "<a onclick=\"return false;\" id=\"my-shortcodes\" title=\"Caldera Shortcodes Builder\" class=\"thickbox\" href=\"?myshortcodeproinsert=insertTB_iframe=1&width=640&height=307\">\n";
    echo "<img src=\"".MYSHORTCODES_URL."images/icon.png\" alt=\"Insert Shortcode\" width=\"15px\" height=\"15px\" />\n";
    echo "</a>\n";
}

function msc_getheader() {
    global $headerContent;
    echo $headerContent;
}
function msc_getfooter() {
    global $footerContent;
    echo $footerContent;
}
function msc_header() {
    global $headerscripts;

    if(!empty($headerscripts)){
    echo "<style type=\"text/css\">\n";
        echo str_replace('}',"}\n",str_replace('; ',';',str_replace(' }',"}",str_replace('{ ','{',str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$headerscripts))))));
    echo "</style>\n";
        $headerscripts = '';
    }
}
function msc_footer() {
    global $footerOutput;
    if(!empty($footerOutput)){
        echo "<script>\n";

        echo $footerOutput;

        echo "</script>\n";
        $footerOutput = '';
    }
}

function msc_menus() {

    global $calderaAdminPage;
    add_menu_page("Caldera - Elements Manager", "My Shortcodes", 'activate_plugins', "my-shortcodes", "msc_adminPage", MYSHORTCODES_URL."images/blank.png");
    $calderaAdminPage = add_submenu_page("my-shortcodes", 'My Shortcodes', 'Shortcode Builder', 'activate_plugins', "my-shortcodes", 'msc_adminPage', MYSHORTCODES_URL."images/blank.png");

    add_action('admin_print_styles-' . $calderaAdminPage, 'msc_styles');
    add_action('admin_print_scripts-' . $calderaAdminPage, 'msc_scripts');
}

function msc_styles() {

    wp_enqueue_style('msc_adminStyle', MYSHORTCODES_URL . 'styles/core.css');
    if (!empty($_GET['action'])) {
        wp_enqueue_style('thickbox');
        wp_enqueue_style('msc_adminbuttons', MYSHORTCODES_URL . 'styles/buttons.css');        
        wp_enqueue_style('codemirror', MYSHORTCODES_URL . 'codemirror/lib/codemirror.css', false);
        wp_enqueue_style('codemirror-simple-hint', MYSHORTCODES_URL . 'codemirror/lib/util/simple-hint.css', false);
        wp_enqueue_style('codemirror-dialog-css', MYSHORTCODES_URL . 'codemirror/lib/util/dialog.css', false);
        wp_enqueue_style('bootstrap-typeahead', MYSHORTCODES_URL . 'styles/dropdown.css', false);
        if($_GET['action'] == 'edit'){
            //wp_enqueue_style('msc_editor', MYSHORTCODES_URL . 'styles/editor.css');
            wp_enqueue_style('msc_editor', MYSHORTCODES_URL . 'editorcss/editor.css');
        }
    }else{
        $Elements = get_option('CE_ELEMENTS');
        if(!empty($Elements)){
            foreach($Elements as $Element){            
                if($Element['elementType'] == 5){
                    wp_enqueue_style('codemirror', MYSHORTCODES_URL . 'codemirror/lib/codemirror.css', false);
                    break;
                }            
            }
        }
        
    }
    wp_enqueue_style('bootstrap-tooltips', MYSHORTCODES_URL . 'styles/tooltips.css', false);
}

function msc_scripts() {

    wp_enqueue_script("jquery");
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script('jquery-ui-droppable');

    if (!empty($_GET['action'])) {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');

        wp_enqueue_script('modernize', MYSHORTCODES_URL . 'libs/js/modernize.js', false);
        wp_enqueue_script('codemirror', MYSHORTCODES_URL . 'codemirror/lib/codemirror.js', false);
        wp_enqueue_script('codemirror-overlay', MYSHORTCODES_URL . 'codemirror/lib/util/overlay.js', false);
        wp_enqueue_script('codemirror-mode-css', MYSHORTCODES_URL . 'codemirror/mode/css/css.js', false);
        wp_enqueue_script('codemirror-mode-js', MYSHORTCODES_URL . 'codemirror/mode/javascript/javascript.js', false);
        wp_enqueue_script('codemirror-mode-xml', MYSHORTCODES_URL . 'codemirror/mode/xml/xml.js', false);
        wp_enqueue_script('codemirror-mode-clike', MYSHORTCODES_URL . 'codemirror/mode/clike/clike.js', false);
        wp_enqueue_script('codemirror-mode-php', MYSHORTCODES_URL . 'codemirror/mode/php/php.js', false);
        wp_enqueue_script('codemirror-mode-htmlmixed', MYSHORTCODES_URL . 'codemirror/mode/htmlmixed/htmlmixed.js', false);
        wp_enqueue_script('codemirror-simple-hint-js', MYSHORTCODES_URL . 'codemirror/lib/util/simple-hint.js', false);
        wp_enqueue_script('codemirror-js-hint', MYSHORTCODES_URL . 'codemirror/lib/util/javascript-hint.js', false);
        wp_enqueue_script('codemirror-dialog-js', MYSHORTCODES_URL . 'codemirror/lib/util/dialog.js', false);
        wp_enqueue_script('codemirror-searchcursor-js', MYSHORTCODES_URL . 'codemirror/lib/util/searchcursor.js', false);
        wp_enqueue_script('codemirror-multiplex-js', MYSHORTCODES_URL . 'codemirror/lib/util/multiplex.js', false);
        wp_enqueue_script('codemirror-search-js', MYSHORTCODES_URL . 'codemirror/lib/util/search.js', false);
        wp_enqueue_script('bootstrap-typeahead', MYSHORTCODES_URL . 'libs/js/typeahead.js', false);        
    }else{
        
        $Elements = get_option('CE_ELEMENTS');
        if(!empty($Elements)){
            foreach($Elements as $Element){            
                if($Element['elementType'] == 5){
                    wp_enqueue_script('codemirror', MYSHORTCODES_URL . 'codemirror/lib/codemirror.js', false);
                    wp_enqueue_script('codemirror-runmode', MYSHORTCODES_URL . 'codemirror/lib/util/runmode.js', false);
                    wp_enqueue_script('codemirror-mode-php', MYSHORTCODES_URL . 'codemirror/mode/php/php.js', false);
                    wp_enqueue_script('codemirror-mode-clike', MYSHORTCODES_URL . 'codemirror/mode/clike/clike.js', false);
                    break;
                }            
            }
        }        
    }
    wp_enqueue_script('bootstrap-tooltips', MYSHORTCODES_URL . 'libs/js/tooltips.js', false);
}
function msc_widgetscripts(){
    wp_enqueue_style('thickbox');
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('minicolors',MYSHORTCODES_URL.'libs/js/minicolors.js');
    
}
function msc_widgetcss(){
    wp_enqueue_style('caldera-widgetcoreCSS', MYSHORTCODES_URL.'styles/core.css');
    wp_enqueue_style('caldera-widgetpanelCSS', MYSHORTCODES_URL.'styles/panel.css');
    wp_enqueue_style('caldera-minicolors', MYSHORTCODES_URL.'styles/minicolors.css');
}
function msc_widgetjs(){
?>
<script>

        jQuery('#widgets-right').on('hover', '.minicolorPicker,.miniColors-trigger-fake',function(){
            jQuery('.miniColors-trigger-fake').remove();
            jQuery('.minicolorPicker').miniColors();
            
        });

        jQuery('#widgets-right').on('click','.msc_uploader', function() {
        formfield = jQuery(this).parent().find('input');
        tb_show('Select or Upload a File', 'media-upload.php?type=file&amp;post_id=0&amp;TB_iframe=true');

        window.send_to_editor = function(html) {
        linkurl = jQuery(html).attr('href');
        imgsrc = jQuery(html).find('img').attr('src');
        if(imgsrc){
            jQuery(formfield).val(imgsrc);
        }else{
         jQuery(formfield).val(linkurl);
        }
        tb_remove();
        }

        return false;
        });
        jQuery('#widgets-right').on('click','.removeRow', function(event){
            jQuery(this).parent().parent().remove();
        });
    jQuery('#widgets-right').on('click','.addRow', function(event){

        event.preventDefault();
        jQuery(this).before('<input type="hidden" value="'+jQuery(this).attr('ref')+'" name="'+jQuery(this).parent().attr('ref')+'" />');

    })

    jQuery('#widgets-right').on('change', ".msc-cat-select",function(){
        var id = this.id;
        var ref = jQuery(this).attr('ref');                
        jQuery('#ele'+id).html('<img alt="" title="" class="" src="<?php echo admin_url(); ?>images/wpspin_light.gif" style="visibility: visible;">');

        var data = {
                action: 'msc_selectedgetcat',
                cat: this.value,
                id:id,
                ele: ref
        };
        //jQuery('#mediaPanel').html('<div class="loading">Loading</div>');
        jQuery.post(ajaxurl, data, function(response) {
            jQuery('#ele'+id).html(response);
        });

    });
    jQuery('#widgets-right').on('change', ".msc-ele-select", function(){
       jQuery('#form_'+this.id).html('');
    });
    jQuery('#widgets-right').on('click',".show-elements-tab", function(event){
       event.preventDefault();
       jQuery(this).toggleClass('active');
       jQuery(jQuery(this).attr('href')).slideToggle();
    });
    function addGroup(id, prefix){
        number = jQuery('.group'+id).length+1;
        //alert(jQuery('.group'+id).length);
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
        var data = {
                action: 'fileatt_addgroup',
                group: id,
                number: number,
                nameprefix: prefix
        };
        jQuery('#mediaPanel').html('<div class="loading">Loading</div>');
        jQuery.post(ajaxurl, data, function(response) {
            jQuery('#tool'+id).before(response);
        });
    }


</script>
<?php
}
function msc_dismisssavepointer(){

    update_option('CE_DISMISS_SAVE', 1);
    die;
}
function msc_setPreviewBGColor(){

    update_option('CE_PREVIEWBGCOLOR', $_POST['color']);
    die;
}
function msc_setPreviewColor(){

    update_option('CE_PREVIEWCOLOR', $_POST['color']);
    die;
}
function msc_selectedgetcat(){
    if(empty($_POST['cat'])){
        die;
    }
    $parts = explode('|', $_POST['ele']);
    $eleName = $parts[0];
    $eleID = $parts[1];
    $Elements = get_option('CE_ELEMENTS');
    $eles = array();
    foreach($Elements as $ID=>$Options){
        $Cat = strtolower($Options['category']);
        if(!empty($Options['state']) && ($Options['elementType'] == 2 || $Options['elementType'] == 3)){
            if($_POST['cat'] == $Cat){
                $eles[strtolower($Options['name'])] = $ID;
            }
        }
    }
    ksort($eles);

    echo "<label for=\"Elements\">Element:</label>\n";
    echo "<select class=\"widefat msc-ele-select\" name=\"".$eleName."\" id=\"".$eleID."\">\n";
    echo "<option value=\"\">Select Element</option>\n";
        foreach($eles as $name=>$element){
            echo "<option value=\"".$element."\">".ucwords($name)."</option>\n";
        }
    echo "</select>\n";
    echo "<span class=\"fbutton\"><input type=\"submit\" class=\"widget-control-save button loadElementControl\" value=\"Load Element\" /></span>";
    die;


}
function msc_adminPage() {
    $settings = get_option('CE_SETTINGS');
    
    /*if(empty($settings['activationID'])){
        
        //$request_string = $this->prepare_request( 'update_check', $request_args );
        //$raw_response = wp_remote_post( $this->api_url, $request_string );
        //echo 'asd';
        include MYSHORTCODES_PATH . 'activate.php';
        return;
    }*/
    
    if (!empty($_GET['action'])) {
        switch ($_GET['action']) {
            case 'settings':
                include MYSHORTCODES_PATH . 'settings.php';
                break;
            case 'edit':
                include MYSHORTCODES_PATH . 'editor.php';
                break;
            default:
                include MYSHORTCODES_PATH . 'admin.php';
                break;
        }
    } else {
        include MYSHORTCODES_PATH . 'admin.php';
    }
}

function msc_configOption($ID, $Name, $Type, $Title, $Config, $caption = false, $inputTags = '') {

    $Return = '';

    switch ($Type) {
        case 'hidden':
            $Val = '';
            if (!empty($Config['_' . $Name])) {
                $Val = $Config['_' . $Name];
            }
            $Return .= '<input type="hidden" name="data[_' . $Name . ']" id="' . $ID . '" value="' . $Val . '" />';
            break;
        case 'textfield':
            $Val = '';
            if (!empty($Config['_' . $Name])) {
                $Val = $Config['_' . $Name];
            }
            $Return .= '<label>'.$Title . '</label> <input type="text" name="data[_' . $Name . ']" id="' . $ID . '" value="' . $Val . '" '.$inputTags.' />';
            break;
        case 'textarea':
            $Val = '';
            if (!empty($Config['_' . $Name])) {
                $Val = $Config['_' . $Name];
            }
            $Return .= '<label>'.$Title . '</label> <textarea name="data[_' . $Name . ']" id="' . $ID . '" cols="70" rows="25">' . htmlentities($Val) . '</textarea>';
            break;
        case 'radio':
            $parts = explode('|', $Title);
            $options = explode(',', $parts[1]);
            $Return .= '<label class="multiLable">'.$parts[0]. '</label>';
            $index = 1;
            foreach ($options as $option) {
                $sel = '';
                if (!empty($Config['_' . $Name])) {
                    if ($Config['_' . $Name] == $index) {
                        $sel = 'checked="checked"';
                    }
                }else{
                    if(strpos($option, '*') !== false){
                        $sel = 'checked="checked"';
                    }
                    
                }
                if (empty($Config)) {
                    if ($index === 1) {
                        $sel = 'checked="checked"';
                    }
                }
                $option = str_replace('*', '', $option);
                $Return .= '<div class="toggleConfigOption"> <input type="radio" name="data[_' . $Name . ']" id="' . $ID . '_' . $index . '" value="' . $index . '" ' . $sel . '/> <label for="' . $ID . '_' . $index . '" style="width:auto;">' . $option . '</label></div>';
                $index++;
            }
            break;
        case 'checkbox':
            $sel = '';
            if (!empty($Config['_' . $Name])) {
                $sel = 'checked="checked"';
            }

            $Return .= '<input type="checkbox" name="data[_' . $Name . ']" id="' . $ID . '" value="1" '.$sel.' /><label for="' . $ID . '" style="margin-left: 10px; width: 570px;">'.$Title.'</label> ';
            break;
    }
    $captionLine = '';
    if(!empty($caption)){
        $captionLine = '<div class="msc_captionLine description">'.$caption.'</div>';
    }
    return '<div class="msc_configOption '.$Type.'" id="config_'.$ID.'">' . $Return . $captionLine.'</div>';
}

function msc_saveElement($Data) {

    if (empty($Data['_ID'])) {
        $Data['_ID'] = strtoupper(uniqid('EL'));
    }
    if (empty($Data['_name'])) {
        $Data['_name'] = $Data['_ID'];
    }
    if (empty($Data['_category'])) {
        $Data['_category'] = 'ungrouped';
        $_POST['data']['_category'] = 'ungrouped';
    }else{
        $Data['_category'] = strtolower($Data['_category']);
    }

    $elements = get_option('CE_ELEMENTS');
    $AlwaysLoads = get_option('CE_ALWAYSLOAD');
    if($Data['_elementType'] == 4){
        $AlwaysLoads[$Data['_ID']]['alwaysLoad'] = $Data['_alwaysLoadPlacement'];
        update_option('CE_ALWAYSLOAD', $AlwaysLoads);
    }else{
        if(!empty($AlwaysLoads[$Data['_ID']]['alwaysLoad'])){
            unset($AlwaysLoads[$Data['_ID']]);
            update_option('CE_ALWAYSLOAD', $AlwaysLoads);
        }
    }
    $elements[$Data['_ID']]['name'] = $Data['_name'];
    $elements[$Data['_ID']]['description'] = $Data['_description'];
    if(!empty($Data['_childof'])){
        unset($Data['_category']);
        $elements[$Data['_ID']]['childof'] = $Data['_childof'];
    }
    if(empty($Data['_childof'])){
        $elements[$Data['_ID']]['category'] = $Data['_category'];
    }
    if(!empty($Data['_removelinebreaks'])){
        $elements[$Data['_ID']]['removelinebreaks'] = 1;
    }else{
        $elements[$Data['_ID']]['removelinebreaks'] = 0;
    }
    if (!empty($Data['_shortcode'])) {
        $Data['_shortcode'] = sanitize_key($Data['_shortcode']);
        $elements[$Data['_ID']]['shortcode'] = $Data['_shortcode'];
    }else{
        $Data['_shortcode'] = sanitize_key($Data['_name']);
        $elements[$Data['_ID']]['shortcode'] = $Data['_shortcode'];

    }
    $elements[$Data['_ID']]['codeType'] = $Data['_shortcodeType'];
    $elements[$Data['_ID']]['elementType'] = $Data['_elementType'];
    if(!isset($elements[$Data['_ID']]['state'])){
        $elements[$Data['_ID']]['state'] = 0;
    }
    if (!empty($Data['_variable'])) {
        foreach ($Data['_variable'] as $Key => $Varible) {
            $Data['_variable'][$Key] = sanitize_key($Varible);
            if(empty($Data['_tabgroup'][$Key])){
                $Data['_tabgroup'][$Key] = 'General Settings';
            }
        }
        $elements[$Data['_ID']]['variables']['names'] = $Data['_variable'];
        $elements[$Data['_ID']]['variables']['defaults'] = $Data['_variableDefault'];
        $elements[$Data['_ID']]['variables']['info'] = $Data['_variableInfo'];
        $elements[$Data['_ID']]['variables']['type'] = $Data['_type'];
        if(!empty($Data['_isMultiple'])){
            $elements[$Data['_ID']]['variables']['multiple'] = $Data['_isMultiple'];
        }
    } else {
        unset($elements[$Data['_ID']]['variables']);
    }

    update_option($Data['_ID'], $Data);
    update_option('CE_ELEMENTS', $elements);

    return $Data['_ID'];
}


function msc_render_element($name = false){
    global $registeredElements;
    if(isset($registeredElements[$name])){
        return $registeredElements[$name];
    }
    return;
}
function msc_getDefaultAtts($ElementID, $atts = false){

    $Element = get_option($ElementID);
    if(!empty($Element['_variable'])){
        if(empty($Element['_elementType'])){
            $Element['_elementType'] = 1;
        }
        if($Element['_elementType'] == 4 || $Element['_elementType'] == 5){
            
            $defaultatts = get_option($ElementID.'_cfg');
            if(!empty($defaultatts)){
                $atts = shortcode_atts($defaultatts, $atts);
            }
        }else{
            $defaultatts = array();
        }
        foreach($Element['_variable'] as $varkey=>$variable){
            if($Element['_type'][$varkey] == 'Custom'){
                $p = explode(',',$Element['_variableDefault'][$varkey], 2);
                $Element['_variableDefault'][$varkey] = trim($p[1]);
            }
            if($Element['_type'][$varkey] == 'Dropdown'){
                if(strpos($Element['_variableDefault'][$varkey], '*') !== false){
                    $opts = explode(',', $Element['_variableDefault'][$varkey]);
                    foreach($opts as $valoption){
                        if(strpos($valoption, '*') !== false){
                            $Element['_variableDefault'][$varkey] = trim(strtok($valoption, '*'));
                            break;
                        }
                    }
                }else{
                    $Element['_variableDefault'][$varkey] = trim(strtok($Element['_variableDefault'][$varkey], ','));
                }

            }
            if(!empty($Element['_isMultiple'][$varkey])){
                $endLoop = true;
                $loopIndex = 1;
                while($endLoop){
                    if(isset($atts[$variable.'_'.$loopIndex])){
                        $defaultatts[$variable.'_'.$loopIndex] = $atts[$variable.'_'.$loopIndex];
                        $varArray[trim($variable)][] = $atts[$variable . '_' . $loopIndex];
                        $loopIndex++;
                    }else{
                        if($loopIndex === 1){
                            $defaultatts[$variable.'_'.$loopIndex] = $Element['_variableDefault'][$varkey];
                            $varArray[trim($variable)][] = $Element['_variableDefault'][$varkey];
                        }
                        $endLoop = false;
                    }
                }
            }else{
                $defaultatts[$variable] = $Element['_variableDefault'][$varkey];
            }

        }

        $atts = shortcode_atts($defaultatts, $atts);

    }else{
        $atts = false;
    }
    if(!empty($varArray)){
        $Out['vararray'] = $varArray;
    }
    $Out['atts'] = $atts;    
    return $Out;
}
function msc_doShortcode($atts, $content, $shortcode) {

    global $footerOutput, $javascript, $jsincludes;

    $elements = get_option('CE_ELEMENTS');
    foreach ($elements as $id => $element) {
        if (!empty($element['shortcode'])) {
            if ($element['shortcode'] === $shortcode) {
                break;
            }
        }
    }
    if (empty($id)) {
        return;
    }
    $Element = get_option($id);
    $attsOutput = msc_getDefaultAtts($id, $atts);
    $atts = $attsOutput['atts'];
    if(!empty($attsOutput['vararray'])){
        $varArray = $attsOutput['vararray'];
    }
    
    
    // Simple [if var] replace // really hackish buyt hey :)
    $pattern = '\[(\[?)(if)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
    preg_match_all('/' . $pattern . '/s', $Element['_mainCode'], $mifs);
    preg_match_all('/' . $pattern . '/s', $Element['_javascriptCode'], $jifs);
    if(!empty($mifs[5]) && !empty($Element['_variable'])){
        foreach($Element['_variable'] as $varID=>$varKey){
            $pattern = '\[(\[?)(if '.$varKey.')\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            preg_match_all('/' . $pattern . '/s', $Element['_mainCode'], $subs);
            if(!empty($subs[3])){
                foreach($subs[3] as $ifVal){
                    $Element['_mainCode'] = str_replace('[if '.$varKey.$ifVal.']', "<?php if('{{".trim($varKey)."}}' != '".trim($ifVal, '=')."'){ ?>", $Element['_mainCode']);
                }
            }
            
            $Element['_mainCode'] = str_replace('[if '.$varKey.']', "<?php if('{{".trim($varKey)."}}' != ''){ ?>", $Element['_mainCode']);
            $Element['_mainCode'] = str_replace('[else]', "<?php }else{ ?>", $Element['_mainCode']);
            $Element['_mainCode'] = str_replace('[/if]', "<?php } ?>", $Element['_mainCode']);
        }        
    }
    
    
    if(!empty($jifs[5]) && !empty($Element['_variable'])){
        foreach($Element['_variable'] as $varID=>$varKey){
            $pattern = '\[(\[?)(if '.$varKey.')\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            preg_match_all('/' . $pattern . '/s', $Element['_javascriptCode'], $subs);
            if(!empty($subs[3])){
                foreach($subs[3] as $ifVal){
                    $Element['_javascriptCode'] = str_replace('[if '.$varKey.$ifVal.']', "<?php if('{{".$varKey."}}' === '".trim($ifVal, '=')."'){ ?>", $Element['_javascriptCode']);
                }
            }
            
            $Element['_javascriptCode'] = str_replace('[if '.$varKey.']', "<?php if('{{".trim($varKey)."}}' != ''){ ?>", $Element['_javascriptCode']);
            $Element['_javascriptCode'] = str_replace('[else]', "<?php }else{ ?>", $Element['_javascriptCode']);
            $Element['_javascriptCode'] = str_replace('[/if]', "<?php } ?>", $Element['_javascriptCode']);
        }        
    }
    

    //$instanceID = msc_checkInstanceID('CE'.strtoupper(md5(serialize($atts)).$id), 'footer');
    $instanceID = msc_checkInstanceID('ce'.$Element['_shortcode'], 'footer');

    $Element['_mainCode'] = str_replace('{{content}}', $content, $Element['_mainCode']);
    $Element['_mainCode'] = str_replace('{{_id_}}',$instanceID, $Element['_mainCode']);

    $Element['_javascriptCode'] = str_replace('{{content}}', $content, $Element['_javascriptCode']);
    $Element['_javascriptCode'] = str_replace('{{_id_}}',$instanceID, $Element['_javascriptCode']);


    $pattern = '\[(\[?)(loop)\b([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
    preg_match_all('/' . $pattern . '/s', $Element['_mainCode'], $loops);
    if (!empty($loops)) {
        foreach ($loops[0] as $loopKey => $loopcode) {
            if (!empty($loops[3][$loopKey]) && !empty($loops[5][$loopKey])) {
                $LoopCodes[$loopKey] = $loops[5][$loopKey];
                $Element['_mainCode'] = str_replace($loopcode, '{{__loop_' . $loopKey . '_}}', $Element['_mainCode']);
                //$Element['_javascriptCode'] = str_replace($loopcode, '{{__loop_' . $loopKey . '_}}', $Element['_javascriptCode']);
            }
        }
    }    
    if (!empty($Element['_variable'])) {
        foreach ($Element['_variable'] as $VarKey => $Variable) {
            $VarVal = $Element['_variableDefault'][$VarKey];            
            if (isset($atts[$Variable])) {
                $VarVal = $atts[$Variable];
            }
            $Element['_mainCode'] = str_replace('{{' . $Variable . '}}', $VarVal, $Element['_mainCode']);
            $Element['_javascriptCode'] = str_replace('{{' . $Variable . '}}', $VarVal, $Element['_javascriptCode']);
        }        
        if (!empty($LoopCodes) && !empty($varArray)) {
            foreach ($LoopCodes as $loopKey => $loopCode) {
                $loopReplace = '';
                if (!empty($varArray[trim($loops[3][$loopKey])])) {
                    foreach ($varArray[trim($loops[3][$loopKey])] as $replaceKey => $replaceVar) {
                        $loopReplace .= $loopCode;
                        foreach ($varArray as $Variable => $VarableArray) {
                            if (!empty($varArray[$Variable][$replaceKey])) {
                                $loopReplace = str_replace('{{' . $Variable . '}}', $varArray[$Variable][$replaceKey], $loopReplace);
                            } else {
                                $loopReplace = str_replace('{{' . $Variable . '}}', '', $loopReplace);
                            }
                            $loopReplace = str_replace('[increment]', $replaceKey, $loopReplace);
                        }
                    }
                    $Element['_mainCode'] = str_replace('{{__loop_' . $loopKey . '_}}', $loopReplace, $Element['_mainCode']);
                    //$Element['_javascriptCode'] = str_replace('{{__loop_' . $loopKey . '_}}', $loopReplace, $Element['_javascriptCode']);
                }
            }
        }
    }
    


    ob_start();
    eval(' ?>' . $Element['_mainCode'] . ' <?php ');
    $Output = ob_get_clean();
    //return $Output;
    return do_shortcode(trim($Output));
}

if(is_admin ()){
    function msc_buildCategoriesDropdown(){

        $Elements = get_option('CE_ELEMENTS');
        $Return = '';
        if(empty($Elements)){
            $Return .= '<h4>You have no active Shortcode or Hybrid elements in any category.</h1>';
            $Out['error'] = 1;
            $Out['message'] = $Return;
            return $Out;
        }

        foreach($Elements as $ID=>$Config){
            $unset = false;
            if(empty($Config['state'])){
                $unset = true;
            }
            if(empty($Config['elementType'])){
                $unset = true;
            }

            if($Config['elementType'] != 1 && $Config['elementType'] != 3){
                $unset = true;
            }
            if($unset === true){
                unset($Elements[$ID]);
            }

        }
        if(empty($Elements)){
            $Return .= '<h4>You have no active Shortcode or Hybrid elements in any category.</h1>';
            $Out['error'] = 1;
            $Out['message'] = $Return;
            return $Out;
        }
        foreach($Elements as $ID=>$Config){
            $Cats[$Config['category']] = $Config['category'];
        }
        ksort($Cats);
        $Return .= "<select class=\"\" id=\"category\" onchange=\"msc_loadCategory();\">\n";
        $Return .= "<option value=\"\"></option>";
        foreach($Cats as $Cat){
            $Return .= "<option value=\"".$Cat."\">".$Cat."</option>\n";
        }
        $Return .= "</select>";


        $Out['error'] = 0;
        $Out['html'] = $Return;

        return $Out;
    }
    function msc_loadElements(){

        $Category = $_POST['category'];
        $Elements = get_option('CE_ELEMENTS');
        $Items = array();
        foreach($Elements as $ID=>$Config){

            if($Config['category'] == $Category){
                $unset = false;
                if(empty($Config['state'])){
                    $unset = true;
                }
                if(empty($Config['elementType'])){
                    $unset = true;
                }

                if($Config['elementType'] != 1 && $Config['elementType'] != 3){
                    $unset = true;
                }
                if($unset === true){
                    unset($Elements[$ID]);
                }else{
                    $Items[$ID] = $Config['name'];
                }
            }
        }

        echo "<select class=\"\" id=\"selectedelement\" onchange=\"msc_loadElement();\">\n";
        echo "<option value=\"\"></option>";
        foreach($Items as $ID=>$Element){
            echo "<option value=\"".$ID."\">".$Element."</option>\n";
        }
        echo "</select>";


        die;
    }
    function msc_loadmedia_page(){

        $page = 12*$_POST['page']-12;


        $args = array(
                'post_type' => 'attachment',
                'numberposts' => 12,
                'offset' => $page,
                'post_status' => null,
                'post_parent' => null, // any parent
                );
            $attachments = get_posts($args);
            if ($attachments) {
                foreach ($attachments as $post) {
                    echo '<div>';
                    setup_postdata($post);
                    echo '<div class="mediaElement">';
                    echo wp_get_attachment_link( $post->ID, array(60,60), false, true, false);
                    echo '<div class="mediaTitleTitle"><a href="'.$post->guid.'">'.$post->post_title.'</a></div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            die;

    }
    function msc_addGroup($Element, $GroupID=false, $number=false, $instance = false){

        if($GroupID == false){
            $GroupID = $_POST['group'];
            $number = $_POST['number'];
            $instance = false;
            $optionID = $_POST['eid'];
            $andDie = true;
        }

    $Element = get_option($optionID);

    echo '<table class="form-table rowGroup group'.$GroupID.'" ref="'.$GroupID.'">';
    echo '<tbody>';
    $first = true;
    foreach($Element['_group'] as $key=>$groupKey){
        if($groupKey == $GroupID){
            if(!empty($Element['_isMultiple'][$key])){
                if(empty($Element['_label'][$key])){
                    $Element['_label'][$key] = ucwords($Element['_variable'][$key]);
                }
                $Default = false;
                if(!empty($instance[$Element['_variable'][$key].'_'.$number])){
                    $Default = $instance[$Element['_variable'][$key].'_'.$number];
                }
                $args = array(
                    'elementID' => $optionID,
                    'key' => $key,
                    'id' => $key.'_'.$number,
                    'name' => $Element['_variable'][$key].'_'.$number,
                    'default' => $Default,
                    'duplicate' => $first
                );
                echo msc_attsConfigFields($args);
                //echo msc_alwaysloadformFieldSet($Element['_type'][$Field], ucwords($Element['_label'][$Field]), $Field, $Element['_variable'][$Field].'_'.$number, $Default, true, $first, $optionID, $Element['_variableInfo'][$Field]);
                $first = false;
            }
        }
    }
    echo '</tbody>';
    echo '</table>';
    if(!empty($andDie)){
        die;
    }

}
function msc_rendersource_preview($ID){
    
    //dump(msc_getDefaultAtts($ID));
    
    
}
function msc_activate(){

    global $wp_version;
    $request_string = array(
        'body' => array(
            'action'  => 'activate_plugin', 
            'transactionid' => $_POST['tid'],
            'api-key' => md5(home_url())
            ),
        'user-agent' => 'WordPress/'. $wp_version .'; '. home_url()
    );    
    $raw_response = wp_remote_post('http://localhost/caldera/api/1/', $request_string );
    //dump($_POST, false);
    //dump($request_string);
    dump($raw_response['body']);
    
    
    die;
}
function msc_dropdown_pages($args = '') {
	$defaults = array(
		'depth' => 0, 'child_of' => 0,
		'selected' => 0, 'echo' => 1,
		'name' => 'page_id', 'id' => '',
		'show_option_none' => '', 'show_option_no_change' => '',
		'option_none_value' => ''
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$pages = get_pages($r);
	$output = '';
	// Back-compat with old system where both id and name were based on $name argument
	if ( empty($id) )
		$id = $name;

	if ( ! empty($pages) ) {
		$output = "<select name='" . esc_attr( $name ) . "' class='" . esc_attr( $class ) . "' id='" . esc_attr( $id ) . "'>\n";
		if ( $show_option_no_change )
			$output .= "\t<option value=\"-1\">$show_option_no_change</option>";
		if ( $show_option_none )
			$output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
		$output .= walk_page_dropdown_tree($pages, $depth, $r);
		$output .= "</select>\n";
	}

	$output = apply_filters('wp_dropdown_pages', $output);

	if ( $echo )
		echo $output;

	return $output;
}
function msc_formField($Element, $Type, $Title, $ID, $Name, $Default = false, $Dup = false, $isFirst = false, $caption = false){

    $class= 'itemRow';
    if(!empty($Dup)){
        $class = '';
    }
    $Return = '<tr valign="top" class="'.$class.'" ref="'.strtok($Name, '_').'"><th scope="row"><label for="field_'.$ID.'">'.$Title.'</label></th>';
    $Return .= '  <td>';

        switch ($Type){
            case 'Dropdown':
                $options = explode(',', $Element['_variableDefault'][$ID]);
                $Return .= '<select name="'.$Name.'" id="'.$Name.'" class="regular-text attrVal" ref="'.$ID.'">';
                    if(strpos($Element['_variableDefault'][$ID], '*') === false){
                        $Return .= '<option value=""></option>';
                    }
                    if($Element['_variableDefault'][$ID] == $Default){
                        $Default = 0;
                    }

                    foreach($options as $option){

                        $sel = '';
                        if(empty($Default)){
                            if(strpos($option, '*') !== false){
                                $sel = 'selected="selected"';
                            }
                        }
                        $option = str_replace('*', '', $option);
                        if($option == $Default){
                            $sel = 'selected="selected"';
                        }
                        $Return .= '<option value="'.$option.'" '.$sel.'>'.ucwords($option).'</option>';
                    }

                $Return .= '</select>';
                break;
            case 'Text':
                $Return .= '<input name="'.$Name.'"  id="'.$Name.'" class="regular-text attrVal" type="text" ref="'.$ID.'" value="'.$Default.'"/>';
                break;
            case 'File':
                $Return .= '<input name="'.$Name.'" id="'.$Name.'" type="text" class="regular-text attrVal" ref="'.$ID.'" value="'.$Default.'" />';
                $Return .= '<div id="uploader_field_'.$ID.'" class="fbutton" onclick="msc_openMediaPanel(\''.$Name.'\');" style="float:none; display:inline;">';
                $Return .= '  <div class="button addFile" style="padding:2px 2px 1px; margin-bottom: 5px; font-weight:normal;">';
                $Return .= '      <i class="icon-file" style="margin-top:-1px;"></i><span> Media Library</span>';
                $Return .= '  </div>';
                $Return .= '</div>';
                break;
            case 'Page':
                $args = array(
                        'depth'   => 0,
                        'child_of'=> 0,
                        'selected'=> $Default,
                        'echo'    => 0,
                        'name'    => $Name,
                        'id'    => $Name,
                        'class'    => 'attrVal',
                        'post_type' => 'page');
                $Return .= msc_dropdown_pages($args);
                //$Return .= '<input name="'.$Name.'"  id="'.$Name.'" class="regular-text attrVal" type="text" ref="'.$ID.'" value="'.$Default.'"/>';
                break;
            case 'Custom':
                //msc_formField($Element, $Type, $Title, $ID, $Name, $Default = false, $Dup = false, $isFirst = false, $caption = false);
                
                dump($Default);
                
                break;

        }

        if($isFirst){
            $Return .= '<div id="remover_field_'.$ID.'" class="fbutton remover" style="float:none; display:inline;">';
            $Return .= '  <div class="button removeRow" style="padding:2px 2px 1px; margin-bottom: 5px; font-weight:normal;">';
            $Return .= '      <i class="icon-remove-sign" style="margin-top:-1px;"></i><span> Remove</span>';
            $Return .= '  </div>';
            $Return .= '</div>';
        }
    if(!empty($caption)){
        $Return .= '<p class="description">'.$caption.'</p>';
    }

    $Return .= '  </td>';
    $Return .= '</tr>';

    return $Return;
}
function msc_load_elementConfig($die = false){
         if(empty($_POST['element'])){
             echo 'Please select a shortcode to continue';
             die;
         }
        $optionID = $_POST['element'];
        $Element = get_option($_POST['element']);
        if(empty($Element['_defaultContent'])){
         $Element['_defaultContent'] = 'Content Goes Here';
        }
        echo '<input type="hidden" id="shortcodekey" value="'.$Element['_shortcode'].'" />';
        echo '<input type="hidden" id="shortcodetype" value="'.$Element['_shortcodeType'].'" />';
        echo '<input type="hidden" id="defaultContent" value="'.$Element['_defaultContent'].'" />';
        
        $Groups = array();
        if(!empty($Element['_variable'])){
            foreach($Element['_variable'] as $Key=>$Var){
                if(empty($Element['_isMultiple'][$Key])){
                    $Groups[$Element['_tabgroup'][$Key]][$Key] = $Var;
                }else{
                    $Groups[$Element['_group'][$Key]][$Key] = $Var;
                }

            }
        }        
        
?>

<div class="wrap poststuff" id="msc_container">
    <div id="main">
        <?php
            $groupCount = count($Groups);        
            $contentclass = '';
            if($groupCount == 1){
                $contentclass = 'solo';
            }        

        ?>
        <div id="ce-nav" class="<?php echo $contentclass; ?>">

            <ul>
                             
                <?php
                    $first = true;                    
                    foreach($Groups as $GroupName=>$vars){
                        $GroupID = sanitize_key($GroupName);
                        $class= '';                        
                        if(!empty($Element['_tabgroup'][$GroupID])){
                            $GroupName = ucwords($Element['_tabgroup'][$GroupID]);
                        }
                        if($first){
                            $class='class="current"';
                        }
                ?>
                <li <?php echo $class; ?>>
                    <a href="#<?php echo $GroupID; ?>" title="<?php echo $GroupName; ?>"><strong><?php echo $GroupName; ?></strong></a>
                </li>                
                
                <?php
                    $first = false;
                    }
                ?>
                
                
                
                
            </ul>

        </div>

        <div id="content" class="<?php echo $contentclass;?>">
            
            
            
            
<?php



if(empty($Element['_variable'])){
        echo 'No configuration nessasary for this plugin, just enjoy it!';
}









$isfirst = true;
foreach($Groups as $GroupName=>$vars){

            $GroupID = sanitize_key($GroupName);
            $class= '';                        
            if(!empty($Element['_tabgroup'][$GroupID])){
                $GroupName = ucwords($Element['_tabgroup'][$GroupID]);
            }
            $display = 'none';
            if($isfirst){
                $display = 'block';
                $isfirst = false;
            }
            
            echo '<div style="display: '.$display.';" class="group" id="'.$GroupID.'">';
            if($groupCount == 1){
                $GroupName = $Element['_name'];                
            }
                echo '<h2>'.$GroupName.'</h2>';
                        
            echo '<p>';
            if(!empty($Element['_variable'][$GroupID])){
                echo '<table class="form-table rowGroup group'.$GroupID.'" id="group'.$GroupID.'" ref="'.$GroupID.'">';
            }else{
                echo '<table class="form-table group'.$GroupID.'" id="group'.$GroupID.'" ref="'.$GroupID.'">';
            }      
            echo '<tbody>';
            foreach($vars as $key=>$var){
                if($Element['_type'][$key] == 'Color Picker'){
                    $enableColorPicker = true;
                }
                if(empty($Element['_label'][$key])){
                    $Element['_label'][$key] = ucwords($Element['_variable'][$key]);
                }
                if(!empty($Element['_isMultiple'][$GroupID])){
                    // go make a multi group
                    if(!isset($isfirst)){
                       $isfirst = true;
                    }else{
                        $isfirst = false;
                    }
                    if(empty($instance[$var.'_1'])){
                        $instance[$var.'_1'] = $Element['_variableDefault'][$key];
                    }
                    $args = array(
                        'elementID' => $optionID,
                        'key' => $key,
                        'id' => $key,
                        'name' => $var.'_1',
                        'default' => $instance[$var.'_1'],
                        'duplicate' => false
                    );
                    echo msc_attsConfigFields($args);
                }else{
                    // go make a single group
                    if(empty($instance[$var])){
                        $instance[$var] = $Element['_variableDefault'][$key];
                    }
                    if(empty($Element['_variableInfo'][$key])){
                         $Element['_variableInfo'][$key] = '';
                    }
                    
                    $args = array(
                        'elementID' => $optionID,
                        'key' => $key,
                        'id' => $key,
                        'name' => $var,
                        'default' => $instance[$var],
                        'duplicate' => false
                    );
                    echo msc_attsConfigFields($args);
                }
            }
            echo '</tbody>';
            echo '</table>';            
            
            echo '</p>';

            $run = true;
            $index = 2;
            while($run){
                if(empty($GroupID) || empty($Element['_variable'][$GroupID])){
                    break;
                }
                if(!empty($instance[$Element['_variable'][$GroupID].'_'.$index])){
                    echo msc_alwaysloadaddGroupSet($GroupID, $index, $instance, $optionID);
                }else{
                    $run = false;
                }
                $index++;
            }



         if(!empty($Element['_variable'][$GroupID])){
            echo '<div class="toolrow" id="tool'.$GroupID.'"><a class="button addRow" href="'.$GroupID.'" ref="'.$optionID.'">Add '.$GroupName.'</a></div>';
         }
         echo '</div>';
        }




?>
            
            
          
            
            
            
            
            
            
            
        </div>
        <div class="clear"></div>

    </div>
    <div style="clear:both;"></div>
</div>

<?php        
        if($die == true){
            return;
        }
        die;
    }


    function msc_deleteElement() {

            $EID = $_POST['EID'];

            $Elements = get_option('CE_ELEMENTS');
            delete_option($EID);
            unset($Elements[$EID]);
            update_option('CE_ELEMENTS', $Elements);
            echo true;
            die();
    }
    function msc_applyElement() {
            
            parse_str(stripslashes($_POST['formData']), $Data);
            if(get_magic_quotes_gpc()){
                $Data = array_map('stripslashes_deep',$Data);
            }
            //dump($Data);
            //if(!empty($Data['data']['_ID'])){
                echo msc_saveElement($Data['data']);
            //}
            die();
    }
    function msc_upgradeElements() {
            $Elements = get_option('CE_ELEMENTS');
            foreach($Elements as $ID=>$cfg){
                if(!isset($cfg['removelinebreaks'])){
                    $Elements[$ID]['removelinebreaks'] = 0;
                }
                if(!isset($cfg['codeType'])){
                    $Elements[$ID]['codeType'] = 1;
                }
                if(!isset($cfg['elementType'])){
                    $Elements[$ID]['elementType'] = 1;
                }
                if(!isset($cfg['state'])){
                    $Elements[$ID]['state'] = 1;
                }
                if(!isset($cfg['shortcode'])){
                    $Elements[$ID]['shortcode'] = $ID;
                }
            }
            update_option('CE_ELEMENTS', $Elements);
            die();
    }

    function msc_ajax_javascript() {
    ?>
    <script type="text/javascript" >
        function msc_upgradeElements(){
                var data = {
                        action: 'upgrade_elements'
                };
                jQuery.post(ajaxurl, data, function(response) {
                    jQuery('.elementUpgradeNodes').css('background', '#4e9700');
                    jQuery('#upgradeElementsButton').parent().html('Elements Upgraded, <a href="admin.php?page=my-shortcodes">Continue</a>');                    
                });
        }
        function msc_deleteElement(eid){
            //if(confirm('Are you sure?')){
                var data = {
                        action: 'delete_element',
                        EID: eid
                };
                jQuery.post(ajaxurl, data, function(response) {
                        if(response == 1){
                            jQuery('#element_'+eid).slideUp('fast', function(){
                                jQuery('#element_'+eid).remove();
                                var newval = parseFloat(jQuery('.current .cs-elementCount').html()-1);
                                if(newval > 0){
                                    jQuery('.current .cs-elementCount').html(newval);
                                }else{
                                    jQuery('.current').slideUp();
                                }
                            });
                        }
                });
            //}else{
            //    jQuery('.buttons_'+eid).slideToggle();
            //}
        }
        function msc_applyElement(eid){
                
                jQuery('#saveIndicator').slideDown(100);
                var data = {
                        action: 'apply_element',
                        EID: eid,
                        formData: jQuery('#elementEditForm').serialize()
                };

                jQuery.post(ajaxurl, data, function(response) {
                    jQuery('#ID').val(response);
                    jQuery('#header .title h2').html('Editing: '+jQuery('#name').val());
                    jQuery('#saveIndicator').slideUp(100);
                });

        }
        function msc_moveElement(eid, cat){
                var data = {
                        action: 'move_element',
                        EID: eid,
                        cat: cat
                };
                jQuery.post(ajaxurl, data, function(response) {
                });
        }
        function msc_setToolTips(state){

                var data = {
                        action: 'set_tooltips',
                        state: state
                };
                jQuery.post(ajaxurl, data, function(response) {
                });
        }
    </script>
    <?php
    }
    function msc_setTooltips(){

        $settings = get_option('CE_SETTINGS');
        if(empty($settings)){
            $settings['disableTooltips'] = 0;
        }
//        vardump($_POST);
        if(!empty($_POST['state'])){
            $settings['disableTooltips'] = 1;
        }else{
            $settings['disableTooltips'] = 0;
        }
        update_option('CE_SETTINGS', $settings);
        die;

    }
    function msc_detectRogues(){
        global $wpdb;
        $Elements = get_option('CE_ELEMENTS');
        if(!empty($Elements)){
            foreach($Elements as $ID=>$cfg){
                $elementsFound[] = "'".$ID."'";
            }
        }        
        $excludes = '';
        if(!empty($elementsFound)){
            $excludes = "AND `option_name` NOT IN (".implode(',',$elementsFound).")";
        }
        $rogue = $wpdb->get_results("SELECT `option_name` FROM `".$wpdb->options."` WHERE `option_name` LIKE 'EL%' AND LENGTH(`option_name`) = 15 ".$excludes.";", ARRAY_A);

        if(!empty($rogue)){
            foreach($rogue as $ElementOption){
                $Element = get_option($ElementOption['option_name']);
                msc_saveElement($Element);
            }
        }
    }
    
    function msc_moveElement(){

        $elements = get_option('CE_ELEMENTS');
        $EID = str_replace('element_', '', $_POST['EID']);
        $element = get_option($EID);
        $cat = $_POST['cat'];
        $elements[$EID]['category'] = $cat;
        $element['_category'] = $cat;
        update_option($EID, $element);
        update_option('CE_ELEMENTS', $elements);
        echo $_POST['EID'];
        die;
    }

    function msc_importScript($file){
        
        $data = unserialize(base64_decode(file_get_contents($file)));
        
        $elements = get_option('CE_ELEMENTS');
        if(empty($data['exportPack'])){
            return 'error: pack was empty. Perhaps the export didn\'t have any elements selected?';
        }
        if(is_array($elements)){
            $elements = array_merge($elements, $data['exportPack']);
        }else{
            $elements = $data['exportPack'];
        }
        update_option('CE_ELEMENTS' ,$elements);
        foreach($data['exportCfg'] as $id=>$cfg){
            update_option($id ,$cfg);
        }
        return $data['exportSettings']['_pluginSet'];
    }
    function msc_exportPlugin($data, $type){

        update_option('_msp_'.$data['_pluginSet'], $data);
        $data['_pluginID'] = sanitize_key($data['_pluginName']);
        $data['_pluginID_UPPER'] = strtoupper($data['_pluginID']);
        $elements = get_option('CE_ELEMENTS');


        $forExport = array();
        $Shortcodes = array();
        $widgetsToInclude = array();
        $WidgetActions = array();
        $settingsAjax = array();
        $exportConfigs = array();
        $newPlugin_path = WP_PLUGIN_DIR.'/'.sanitize_file_name(strtolower($data['_pluginName']));
        $uploadVars = wp_upload_dir();
        $assetsToCopy = array();
        $Widgets = array();
        $Posttypes = array();
        $alwaysLoads= array();
        $pluginAlwaysLoadIncludes = array();
        $slugsAvailable = array();
        $pluginFunctionsInclude = '';
        $hasColorPicker = array();
        if($type == 'script' || $type == 'standalone'){
            
            $outData = array();
            $outData['exportSettings'] = $data;
            foreach($elements as $id=>$element){               
                if(sanitize_key($element['category']) == $data['_pluginSet'] || ($data['_pluginSet'] == '__allactive____' && $element['state'] == 1)){
                    
                    $outData['exportPack'][$id] = $element;
                    $Element = get_option($id);                    
                    $outData['exportCfg'][$id] = $Element;
                }
            }            
            if($type == 'script'){
                $filename = $data['_pluginSet'];
                if($data['_pluginSet'] == '__allactive____'){
                    $filename = 'all_active';
                }

            $outData = gzencode(base64_encode(serialize($outData)),9);
                ini_set('zlib.output_compression','Off');
                header('Content-Type: application/x-download');
                header('Content-Encoding: gzip');
                header('Content-Length: '.strlen($outData));
                header('Content-Disposition: attachment; filename="'.$filename.'.msc"');
                header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
                header('Pragma: no-cache');
                echo $outData;
                die;
            }

        }
        
    }


}












function msc_attsConfigFields($args){
    global $phpincludes;
    if(empty($args['elementID']) || empty($args['key'])){
        return 'Need to define the element and key.';
    }    
    $Element = get_option($args['elementID']);     
    $argDefault = array(
        'elementID' => '',
        'key' => '',
        'id' => $Element['_variable'][$args['key']],
        'name' => $Element['_variable'][$args['key']],
        'default' => trim($Element['_variableDefault'][$args['key']]),
        'duplicate' => false
    );
    $args = wp_parse_args($args, $argDefault);
    
    $class= 'itemRow';
    if(!empty($Dup)){
        $class = '';
    }  
    if(empty($Element)){
        return 'Element ID "'.$args['elementID'].'" is invalid.';
    }

    $Return = '<tr valign="top" class="'.$class.'"><th scope="row"><label for="'.$args['id'].'">'.$Element['_label'][$args['key']].'</label></th>';
    $Return .= '  <td>';

    
        switch ($Element['_type'][$args['key']]){
            case 'Dropdown':
                $options = explode(',', $Element['_variableDefault'][$args['key']]);
                $Return .= '<select name="'.$args['name'].'" class="regular-text" ref="'.$args['id'].'" id="field_'.$args['id'].'">';

                    $Return .= '<option value=""></option>';
                    foreach($options as $option){

                        $sel = '';
                        if(strpos($option, '*') !== false && ($args['default'] == $Element['_variableDefault'][$args['key']])){
                            $sel = 'selected="selected"';
                        }
                        $option = str_replace('*', '', $option);
                        if($option == $args['default']){
                            $sel = 'selected="selected"';
                        }
                        $Return .= '<option value="'.$option.'" '.$sel.'>'.ucwords($option).'</option>';
                    }

                $Return .= '</select>';
                break;
            case 'Checkbox':
                $options = explode(',', $Element['_variableDefault'][$args['key']]);
                    foreach($options as $key=>$option){
                        $idin = uniqid();
                        $sel = '';
                        if(strpos($option, '*') !== false && ($args['default'] == $Element['_variableDefault'][$args['key']])){
                            $sel = 'checked="checked"';
                        }
                        $option = str_replace('*', '', $option);
                        $label = ucwords($option);
                        if(strpos($option, ';') !== false){
                            $opts = explode(';', $option);
                            $option = $opts[0];
                            $lable = $opts[1];
                        }
                        if($option == $args['default']){
                            $sel = 'checked="checked"';
                        }
                        $Return .= '<label for="'.$args['id'].'_'.$idin.'">';
                        $Return .= '<input type="checkbox" value="'.$option.'" id="'.$args['id'].'_'.$idin.'" name="'.$args['name'].'" '.$sel.'/> ';
                        $Return .= $label.'</label><br />';
                        
                    }
                break;            
            case 'Radio':
                $options = explode(',', $Element['_variableDefault'][$args['key']]);
                    foreach($options as $key=>$option){
                        $idin = uniqid();
                        $sel = '';
                        if(strpos($option, '*') !== false && ($args['default'] == $Element['_variableDefault'][$args['key']])){
                            $sel = 'checked="checked"';
                        }
                        $option = str_replace('*', '', $option);
                        $label = ucwords($option);
                        if(strpos($option, ';') !== false){
                            $opts = explode(';', $option);
                            $option = $opts[0];
                            $lable = $opts[1];
                        }
                        if($option == $args['default']){
                            $sel = 'checked="checked"';
                        }
                        $Return .= '<label for="'.$args['id'].'_'.$idin.'">';
                        $Return .= '<input type="radio" value="'.$option.'" id="'.$args['id'].'_'.$idin.'" name="'.$args['name'].'" '.$sel.'/> ';
                        $Return .= $label.'</label><br />';
                        
                    }
                break;            
            case 'Color Picker':
                $Return .= '<input name="'.$args['name'].'" class="small-text minicolorPicker" type="text" ref="'.$args['id'].'" id="'.$args['id'].'" value="'.$args['default'].'" style="width:115px;"/><a href="#" style="background-color: '.$args['default'].'" class="miniColors-trigger miniColors-trigger-fake"></a>';
                break;
            case 'Text':
            case 'Text Field':
                $Return .= '<input name="'.$args['name'].'" class="regular-text" type="text" ref="'.$args['id'].'" id="'.$args['id'].'" value="'.$args['default'].'"/>';
                break;
            case 'Text Box':
                $Return .= '<textarea name="'.$args['name'].'" class="large-text" rows="5" type="text" id="'.$args['id'].'" ref="'.$args['id'].'">'.htmlspecialchars($args['default']).'</textarea>';
                break;
            case 'File':
                $Return .= '<input name="'.$args['name'].'" type="text" class="regular-text" ref="'.$args['id'].'" id="'.$args['id'].'" value="'.$args['default'].'" />';
                $Return .= '<div id="uploader_'.$args['id'].'" class="fbutton msc_uploader" style="float:none; display:inline;">';
                $Return .= '  <div class="button addFile" style="padding:2px 2px 1px; margin-bottom: 5px; font-weight:normal;">';
                
                $Return .= '      <i class="icon-file" style="margin-top:-1px;"></i><span> Select File</span>';
                
                $Return .= '  </div>';
                $Return .= '</div>';
                break;
            case 'Page Selector':
            case 'Page':
                $pageargs = array(
                        'depth'   => 0,
                        'child_of'=> 0,
                        'selected'=> $args['default'],
                        'echo'    => 0,
                        'name'    => $args['name'],
                        'id'    => $args['id'],
                        'class'    => 'attrVal',
                        'post_type' => 'page');
                $pageArgsDefault = array(
                        'depth' => 0, 'child_of' => 0,
                        'selected' => 0, 'echo' => 1,
                        'name' => 'page_id', 'id' => '',
                        'show_option_none' => '', 'show_option_no_change' => '',
                        'option_none_value' => ''
                );

                $r = wp_parse_args( $pageargs, $pageArgsDefault );
                extract( $r, EXTR_SKIP );

                $pages = get_pages($r);
                $output = '';
                if ( empty($id) )
                        $id = $name;

                if ( ! empty($pages) ) {
                        $output = "<select name='" . esc_attr( $name ) . "' class='" . esc_attr( $class ) . "' id='" . esc_attr( $id ) . "'>\n";
                        if ( $show_option_no_change )
                                $output .= "\t<option value=\"-1\">$show_option_no_change</option>";
                        if ( $show_option_none )
                                $output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
                        $output .= walk_page_dropdown_tree($pages, $depth, $r);
                        $output .= "</select>\n";
                }

                $Return .= apply_filters('wp_dropdown_pages', $output);
                break;
            case 'Custom':
                //msc_formField($Element, $Type, $Title, $ID, $Name, $Default = false, $Dup = false, $isFirst = false, $caption = false);
                

                $Custom = explode(',', $argDefault['default']);
                $func = $Custom[0];
                if($args['default'] == $argDefault['default']){
                   if(!empty($Custom[1])){
                       $args['default'] = $Custom[1];
                   }else{
                       $args['default'] = '';
                   }
                }
                if (!empty($Element['_phpCode'])){
                    if(empty($phpincludes[$Element['_ID']])){
                        eval($Element['_phpCode']);
                        $phpincludes[$Element['_ID']] = true;
                    }
                }                
                $Return .= $func($args);
                
                break;
                
        }
        

        if(!empty($args['duplicate'])){
            $Return .= '<div id="remover_'.$args['id'].'" class="fbutton remover" style="float:none; display:inline;">';
            $Return .= '  <div class="button removeRow" style="padding:2px 2px 1px; margin-bottom: 5px; font-weight:normal;">';
            $Return .= '      <i class="icon-remove-sign" style="margin-top:-1px;"></i><span> Remove</span>';
            $Return .= '  </div>';
            $Return .= '</div>';
        }
    if(!empty($Element['_variableInfo'][$args['key']])){
        $Return .= '<p class="description">'.$Element['_variableInfo'][$args['key']].'</p>';
    }
    $Return .= '  </td>';
    $Return .= '</tr>';

    return $Return;
}

function dump($a, $die = true){
    echo '<pre>';
    print_r($a);
    echo '</pre>';
    if($die){
        die;
    }
}


?>