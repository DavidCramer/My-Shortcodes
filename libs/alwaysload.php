<?php
$footScripts = '';
add_action('admin_menu', 'msc_alwaysloadsettings');
function msc_alwaysloadsettings() {

        $Elements = get_option('CE_ALWAYSLOAD');
        $Elements = get_option('CE_ELEMENTS');
        //vardump($Elements);
        if(!empty($Elements)){
            foreach($Elements as $ID=>$Element){
                if(!empty($Element['variables']) && $Element['state'] == 1 && ($Element['elementType'] == 4 || $Element['elementType'] == 5)){
                    $ElementCfg = get_option($ID);
                    $adminPage = add_options_page($ElementCfg['_name'].' Settings', $ElementCfg['_name'], 'manage_options', strtolower($ID), 'msc_alwaysloadsetuppage');
                    add_action('admin_head-'.$adminPage, 'msc_widgetcss');
                    add_action('admin_head-'.$adminPage, 'msc_widgetscripts');
                    if(in_array('Color Picker', $Element['variables']['type'])){
                        wp_enqueue_style('minicolors', MYSHORTCODES_URL.'styles/minicolors.css');
                        wp_enqueue_script('minicolors', MYSHORTCODES_URL.'libs/js/minicolors.js');
                    }
                }
            }
        }
}
function msc_alwaysloadaddGroupSet($GroupID=false, $number=false, $instance = false, $optionID = false){
        if($GroupID == false){
            $GroupID = $_POST['group'];
            $number = $_POST['number'];
            $instance = false;
            $optionID = $_POST['oID'];
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
function msc_alwaysloaddropdown_pages($args = '') {
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

function msc_alwaysloadsetuppage(){
    global $footScripts;
    $optionID = strtoupper($_GET['page']);
    $Element = get_option($optionID);

  
  if(!empty($_POST)){
      if(wp_verify_nonce($_POST['_wpnonce'],'msc_alwaysloadnounce')){
          unset($_POST['_wpnonce']);
          unset($_POST['_wp_http_referer']);
          unset($_POST['submit']);
           update_option($optionID.'_cfg', $_POST);
        $message = '<div class="update" id="message"><p>Settings Updated.</p></div>';
      }
 }
?>
<form action="" method="post">

<?php
    wp_nonce_field('msc_alwaysloadnounce');
    $instance = get_option($optionID.'_cfg');    
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
    if(empty($instance)){
        $instance = array();
        if(!empty($Element['_variable'])){
            foreach($Element['_variable'] as $key=>$var){
                if(!empty($Element['_isMultiple'][$key])){
                    $var = $var.'_1';
                }
                $instance[$var] = $Element['_variableDefault'][$key];

            }
        }
        if(!empty($Element['_assetLabel'])){
            foreach($Element['_assetLabel'] as $assetKey=>$assetLabel){
                foreach($instance as $instanceKey=>$instanceVal){
                    $instance[$instanceKey] = str_replace('{{'.$assetLabel.'}}', $Element['_assetURL'][$assetKey], $instanceVal);
                }

            }
        }
        //update_option($optionID.'_cfg', $instance);
    }

    if(!empty($Groups[0])){
        $holder[0] = $Groups[0];
        unset($Groups[0]);
        foreach($Groups as $key=>$Group){
        $holder[$key] = $Group;
        }
        $Groups = $holder;
    }

    ?>

<div class="wrap poststuff" id="msc_container">

    <div id="header">
        <div class="title">
            <h2><?php echo $Element['_name']; ?></h2><sub><?php echo $Element['_description']; ?></sub>
        </div>
        <div class="clear"></div>
    </div>
    <?php
    if(!empty($message)){
    ?>    
    <div class="save_bar_tools">
        <?php echo $message; ?>
    </div>
    <?php
    }
    ?>
    <div id="main">
        <div id="ce-nav">

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

        <div id="content">
            
            
            
            
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
    <div class="save_bar_top" style="padding:5px; height: 22px;">
        <button type="submit" class="button-primary">Save Changes</button>
    </div>

    <div style="clear:both;"></div>
</div>
</form>
<script type="text/javascript">

    jQuery(document).ready(function(){
        
        
        jQuery('#ce-nav li a').click(function(){
            jQuery('#ce-nav li').removeClass('current');
            jQuery('.group').hide();
            jQuery(''+jQuery(this).attr('href')+'').show();
            jQuery(this).parent().addClass('current');
            return false;
        });


      if(window.location.hash){
        var hash = window.location.hash.substring(1);
        jQuery('.current').removeClass('current');

        var vals = hash.split('&');        

        jQuery('a[href="#'+vals[0]+'"]').parent().addClass('current');
        jQuery('#content .group').hide();
        jQuery('#'+vals[0]).show();
        jQuery('#element_'+vals[1]+' .cs-elementItem.elementMain').addClass('lastEdited');

      }
      <?php
      
      if(isset($enableColorPicker)){
          echo "jQuery('.miniColors-trigger-fake').remove();";          
          echo "jQuery('.minicolorPicker').miniColors();";          
      }
      
      ?>
    });
</script>
<script>
    jQuery('#msc_container').on('click', '.msc_uploader',function() {
     formfield = jQuery(this).parent().parent().find('input');
     tb_show('Select or Upload a File', 'media-upload.php?type=file&amp;post_id=0&amp;TB_iframe=true');

        window.send_to_editor = function(html) {
         if(jQuery('img', html).length){
            linkurl = jQuery('img', html).attr('src');
         }else{
            linkurl = jQuery(html).attr('href');
         }

         jQuery(formfield).val(linkurl);
         tb_remove();
        }

     return false;
    });
    jQuery('#msc_container').on('click','.remover', function(){

        var id = jQuery(this).parent().parent().parent().parent().attr('ref');
        jQuery(this).parent().parent().parent().parent().remove();
        var count = 1;
        jQuery('.group'+id).each(function(){
            jQuery(this).find('[name]').each(function(){
                var name = jQuery(this).attr('name').split('_');
                jQuery(this).attr('name', name[0]+'_'+count);
            })
            count++;
        });

    })
    jQuery('.addRow').click(function(event){

        event.preventDefault();
        addGroup(jQuery(this).attr('href'), jQuery(this).attr('ref'));

    })

    function addGroup(id, oID){

        number = jQuery('.group'+id).length+1;

        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
        var data = {
                action: 'msc_alwaysloadaddgroupSet',
                group: id,
                oID: oID,
                number: number
        };
        jQuery('#mediaPanel').html('<div class="loading">Loading</div>');
        jQuery.post(ajaxurl, data, function(response) {
            jQuery('#tool'+id).before(response);
        });
    }
</script>
<?php

}



?>
