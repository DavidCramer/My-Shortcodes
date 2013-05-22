<div id="settingsPane" class="config"><?php
global $shortcode_tags;
$Elements = get_option('CE_ELEMENTS');
$cats = array();
if(!empty($Elements)){
    foreach($Elements as $el){
        $cat = strtolower($el['category']);
        $cats[$cat] = '"'.$cat.'"';
    }
}

if(!empty($Element['_shortcode'])){
    if(!empty($shortcode_tags[$Element['_shortcode']])){
        if($shortcode_tags[$Element['_shortcode']] == 'msc_doShortcode'){
            unset($shortcode_tags[$Element['_shortcode']]);
        }
    }
}

//vardump($shortcode_tags);
/* 
 * element settings
 * 
 */
if(empty($Element['_defaultContent'])){
    $Element['_defaultContent'] = 'Content goes here';
}
if(empty($Element['_shortcodeType'])){
    $Element['_shortcodeType'] = '1';
}
if(!isset($Element['_ID'])){
    $Element['_ID'] = '';
}
//if(!empty($Element['_ID'])){
    echo msc_configOption('ID', 'ID', 'hidden', 'element ID', $Element);
//}
echo msc_configOption('name', 'name', 'textfield', 'Element Name', $Element);
echo msc_configOption('description', 'description', 'textfield', 'Element Description', $Element);
//if(empty($_GET['childof'])){
    echo msc_configOption('category', 'category', 'textfield', 'Category', $Element, false, 'autocomplete="off"');
    //echo msc_configOption('subcategory', 'subcategory', 'textfield', 'Category', $Element, false, 'autocomplete="off"');
//}
echo msc_configOption('shortcode', 'shortcode', 'textfield', 'Slug', $Element);

echo '<h3>Element Type</h3>';

//echo msc_configOption('elementType', 'elementType', 'radio', 'Element Type|Shortcode*, Widget, Hybrid (Shortcode & Widget), Always Load, Code', $Element);
if(empty($Element['_elementType'])){
    $Element['_elementType'] = 1;
}
?>

<div id="config_elementType" class="msc_configOption radio">
    <label class="multiLable">Element Type</label>
    <div class="toggleConfigOption"> 
        <input type="radio" checked="checked" value="1" id="elementType_1" name="data[_elementType]" <?php if($Element['_elementType'] == '1'){ echo 'checked="checked"'; } ?> >
        <label style="width:auto;" for="elementType_1">Shortcode</label>
    </div>
    <div class="toggleConfigOption">
        <input type="radio" value="2" id="elementType_2" name="data[_elementType]" <?php if($Element['_elementType'] == '2'){ echo 'checked="checked"'; } ?>>
        <label style="width:auto;" for="elementType_2"> Widget</label>
    </div>
    <div class="toggleConfigOption">
        <input type="radio" disabled="disabled" name="data[_elementType]">
        <label style="width:auto;" for="elementType_3"> Hybrid (Shortcode &amp; Widget) <span class="description">Only available in Pro</span></label>
    </div>
    <div class="toggleConfigOption">
        <input type="radio" disabled="disabled" name="data[_elementType]">
        <label style="width:auto;" for="elementType_4"> Always Load <span class="description">Only available in Pro</span></label>
    </div>
    <div class="toggleConfigOption">
        <input type="radio" disabled="disabled" name="data[_elementType]">
        <label style="width:auto;" for="elementType_5"> Code <span class="description">Only available in Pro</span></label>
    </div>
    <div class="toggleConfigOption">
        <a class="button-primary" target="_blank" href="http://myshortcodes.cramer.co.za/pro-version/">Find out about the Pro Version</a>
    </div>

</div>
<?php
echo msc_configOption('alwaysLoadPlacement', 'alwaysLoadPlacement', 'radio', 'Template Placment|Disable Template*, Header, Prepend Content, Append Content, Footer', $Element);



echo msc_configOption('widgetWrap', 'widgetWrap', 'radio', 'Use Widget Style|No,Yes*', $Element, 'Use Themes Widget Style');
echo msc_configOption('widgetTitle', 'widgetTitle', 'radio', 'Widget Title Field|No,Yes*', $Element);
echo msc_configOption('shortcodeType', 'shortcodeType', 'radio', 'Content Box|No*,Yes', $Element);
echo msc_configOption('defaultContent', 'defaultContent', 'textfield', 'Default Content', $Element);
echo '<h3>Processing</h3>';
echo msc_configOption('removelinebreaks', 'removelinebreaks', 'checkbox', 'Remove linebreaks', $Element);


?>
<script>

    jQuery('#tabid1').click(function(){
        jQuery('#editorPane .tabs a').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('#editorPane .area article').hide();
        jQuery(jQuery(this).attr('href')).show();
    });

    if(jQuery('#shortcodeType_1').attr('checked')){
        jQuery('#config_defaultContent').hide();
        jQuery('#config_widgetWrap').hide();
    }
    if(!jQuery('#elementType_2').attr('checked') && !jQuery('#elementType_3').attr('checked')){
        jQuery('#config_widgetTitle').hide();
    }
    jQuery('#shortcodeType_2').click(function(){
        if(jQuery(this).attr('checked')){
            jQuery('#config_defaultContent').slideDown();
        }
    });
    jQuery('#shortcodeType_1').click(function(){
        if(jQuery(this).attr('checked')){
            jQuery('#config_defaultContent').slideUp();
            jQuery('#config_widgetWrap').slideUp();
        }
    });

    if(jQuery('#elementType_4').attr('checked')){
        jQuery('#config_shortcodeType').hide();
    }else{
        jQuery('#config_alwaysLoadPlacement').hide();
    }
    jQuery("input[name='data[_elementType]']").change(function(e){
            if(jQuery('#elementType_4').attr('checked')){                
                jQuery('#config_alwaysLoadPlacement').slideDown();
                jQuery('#config_shortcodeType').slideUp();
                if(jQuery('#shortcodeType_1').attr('checked')){
                    jQuery('#config_defaultContent').slideUp();
                }
            }else{
                jQuery('#config_alwaysLoadPlacement').slideUp();
                jQuery('#config_shortcodeType').slideDown();
                if(jQuery('#shortcodeType_2').attr('checked')){
                    jQuery('#config_defaultContent').slideDown();
                }
            }
            if(jQuery('#elementType_2').attr('checked') || jQuery('#elementType_3').attr('checked')){
                jQuery('#config_widgetTitle').slideDown();
                jQuery('#config_widgetWrap').slideDown();
            }else{
                jQuery('#config_widgetTitle').slideUp();
                jQuery('#config_widgetWrap').slideUp();
            }
    });
    jQuery("input[name='data[_alwaysLoadPlacement]']").change(function(e){
        if(jQuery('#alwaysLoadPlacement_1').attr('checked')){
            jQuery('#tab_ctb_3').slideUp();
        }else{
            jQuery('#tab_ctb_3').slideDown();
        }
    });


    <?php
    $usedCodes = array();
    foreach($shortcode_tags as $code=>$func){
        $usedCodes[] = '"'.$code.'"';
    }

    echo "var cats = new Array(".implode(',', $cats).");\n";
    echo "var used = new Array(".implode(',', $usedCodes).");\n";

    ?>
    if(jQuery.inArray(jQuery('#shortcode').val(), used) >= 0){
            jQuery('#shortcode').css('borderColor', '#ff0000');
            jQuery('#shortcode').after(' <span id="SCerrorMessage" class="description">This shortcode is already in use and will cause problems</span>');
    }
    jQuery('#shortcode').keyup(function(){
        if(jQuery.inArray(this.value, used) >= 0){
            jQuery('#shortcode').css('borderColor', '#ff0000');
            jQuery('#shortcode').after(' <span id="SCerrorMessage" class="description">This shortcode is already in use and will cause problems</span>');
        }else{
            jQuery('#shortcode').css('borderColor', '');
            jQuery('#SCerrorMessage').remove();
        }
    })


    

    jQuery('#category').typeahead({source: cats});

</script></div>