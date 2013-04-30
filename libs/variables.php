<div style="padding: 3px 0 10px;">
    <button type="button" onclick="msc_expandVariables();" class="cbutton" style="float:right;"><i class="icon-resize-full"></i> Expand All</button>
    <button type="button" onclick="msc_contractVariables();" class="cbutton" style="float:right; margin-right: 10px;"><i class="icon-resize-small"></i> Contract All</button>
    <button type="button" onclick="msc_addVariable();" class="cbutton"><i class="icon-plus"></i> Add Attribute</button>
</div>
<div id="variablePane">
<?php

    $types = array(
        'Text Field',
        'Text Box',
        'Dropdown',
        'Checkbox',
        'Radio',
        'Color Picker',
        'File',
        'Page Selector',
        //'Custom',
    );
    //vardump($Element, false);
    if(!empty($Element['_variable'])){
        foreach($Element['_variable'] as $key=>$var){
            $default = '';
            if(isset($Element['_variableDefault'][$key])){
                $default = $Element['_variableDefault'][$key];
            }
            $label = ucwords($var);
            if(!empty($Element['_label'][$key])){
                $label = $Element['_label'][$key];
            }
            $info = '';
            if(!empty($Element['_variableInfo'][$key])){
                $info = $Element['_variableInfo'][$key];
            }
            $tabgroup = 'General Settings';
            $tabGroupShown = 'block';      
            $tabGroupLabel = 'Group';
            if(!empty($Element['_tabgroup'][$key])){
                $tabgroup = $Element['_tabgroup'][$key];                
            }
            echo '<div id="'.$key.'" class="attributeItem">';


            echo '<div class="attribute-row-left">';
                echo '<div class="attributeField"><label for="label'.$key.'">Label</label><input class="labelbox" ref="'.$key.'" type="text" value="'.$label.'" id="name'.$key.'" name="data[_label]['.$key.']" style="width: 100px;"></div>';                
                echo '<div class="attributeField tiny"><label for="slug'.$key.'">Slug</label><input class="slugbox" ref="'.$key.'" type="text" value="'.$var.'" id="slug'.$key.'" name="data[_variable]['.$key.']" style="width: 100px;"></div>';                
                echo '<div class="attributeField"><label for="type'.$key.'">Type</label><select name="data[_type]['.$key.']" id="type'.$key.'" style="width: 100px;">';                
                foreach($types as $type){
                    $sel = '';
                    if($Element['_type'][$key] == $type){
                        $sel = 'selected="selected"';
                    }
                    echo '<option value="'.$type.'" '.$sel.'>'.$type.'</option>';
                }
                echo '</select></div>';
                $sel = '';
                if(!empty($Element['_isMultiple'][$key])){
                    $sel = 'checked="checked"';
                }
                echo '<div class="attributeField extend"><label for="multiple'.$key.'">Mulitple</label><input type="checkbox" class="multi-check" value="1" id="multiple'.$key.'" ref="'.$key.'" name="data[_isMultiple]['.$key.']" '.$sel.' /></div>';
                        $showGroup = 'display:none;';
                        if(!empty($Element['_isMultiple'][$key])){
                           $showGroup = '';
                           $tabGroupShown = 'none';
                        }
                        echo '<div class="attributeField extend"><span id="group'.$key.'" style="'.$showGroup.'"><label for="select_'.$key.'">Group with</label>';
                        echo '<select id="select_multiple'.$key.'" class="groupSelect" name="data[_group]['.$key.']">';
                        if(!empty($Element['_isMultiple'][$key])){
                            foreach($Element['_isMultiple'] as $mkey=>$mval){
                                $sel = '';
                                if(!empty($Element['_isMultiple'][$key])){
                                    if($Element['_group'][$key] == $mkey){
                                        $sel = 'selected="selected"';
                                    }
                                }
                                echo '<option ref="'.$key.'" value="'.$mkey.'" '.$sel.'>'.$Element['_variable'][$mkey].'</option>';
                                //echo '<option value="'.$mkey.'" '.$sel.'>'.$Element['_group'][$mkey].' -- '.$mkey.' -- '.$key.'</option>';

                            }
                        }
                        echo '</select></span>';
                echo '</div>';
                
                if(!empty($Element['_group'])){
                    if(in_array($key, $Element['_group'])){
                        $tabGroupLabel = 'Group label';
                        $tabGroupShown = 'block';
                        if($tabgroup == 'General Settings'){
                            $tabgroup = $label;
                        }
                    }
                }
                
                echo '<div class="attributeField" id="tabgroup'.$key.'" style="display:'.$tabGroupShown.'"><label for="tabgroupfield'.$key.'">'.$tabGroupLabel.'</label><input class="tabgroupbox" ref="'.$key.'" type="text" value="'.$tabgroup.'" id="tabgroupfield'.$key.'" name="data[_tabgroup]['.$key.']" style="width: 100px;"></div>';
            echo '</div>';
            
            echo '<div class="attribute-row-right">';
                echo '<div class="attributeField extend"><label for="info'.$key.'">Info</label><textarea id="info'.$key.'" name="data[_variableInfo]['.$key.']">'.htmlspecialchars($info).'</textarea></div>';
                echo '<div class="attributeField"><label for="default'.$key.'">Default</label><textarea id="default'.$key.'" name="data[_variableDefault]['.$key.']">'.htmlspecialchars($default).'</textarea></div>';            
            echo '</div>';
            echo '<div class="clear"></div>';
            echo '<div class="attributeFooter">';                
                echo '<a class="removal cbutton cbutton-sml" href="#" onclick="jQuery(this).slideUp(130,function(){jQuery(this).parent().find(\'.confirm\').slideDown(130)}); return false;"><i class="icon-remove"></i> Remove Attribute&nbsp;</a>';
                echo '<a class="confirm cbutton cbutton-sml" href="#" onclick="jQuery(\'#'.$key.'\').slideUp(130, function(){jQuery(this).remove()}); return false;" style="display:none;"><i class="icon-check"></i> Confirm?&nbsp;</a> <a class="confirm cbutton cbutton-sml" href="#" onclick="jQuery(this).parent().find(\'.confirm\').slideUp(130, function(){jQuery(this).parent().find(\'.removal\').slideDown(130)}); return false;" style="display:none;"><i class="icon-share-alt"></i> Cancel&nbsp;</a>';
            echo '</div>';
        echo '</div>';
        }
    }
?>
</div>


<script type="text/javascript">
        
    jQuery('#variablePane').on('click', 'atab2', function(){
        jQuery('.attributeItem').find('.extend').hide();
    });
    jQuery('#variablePane').on('click','.attributeItem', function(){
        var active = jQuery(this);
        jQuery('.attributeField').show();
        active.find('.attributeField.tiny').show();
        jQuery('.attributeItem').not(active).find('.attributeField:not(.tiny)').hide();
    });
    function msc_expandVariables(){
        jQuery('.attributeField').show();
    }
    function msc_contractVariables(size){
        //if(!size){
            //jQuery('.extend').hide();
        //}else{
            jQuery('.attributeField:not(.tiny)').hide();
        //}
    }
    function msc_addVariable(){
        
        var rowID = randomUUID();
        //jQuery('<div class="attributeItem" id="'+rowID+'" style=""><div class="attribute-row-left"><div class="attributeField"><label for="label'+rowID+'">Label</label><input type="text" style="width: 100px;" name="data[_label]['+rowID+']" id="name'+rowID+'" value="'+rowID+'" ref="'+rowID+'" class="labelbox"></div><div class="attributeField tiny"><label for="slug'+rowID+'">Slug</label><input type="text" style="width: 100px;" name="data[_variable]['+rowID+']" id="slug'+rowID+'" value="'+rowID+'" ref="'+rowID+'" class="slugbox"></div><div class="attributeField"><label for="type'+rowID+'">Type</label><select style="width: 100px;" id="type'+rowID+'" name="data[_type]['+rowID+']"><option selected="selected" value="Text Field">Text Field</option><option value="Text Box">Text Box</option><option value="Dropdown">Dropdown</option><option value="Checkbox">Checkbox</option><option value="Radio">Radio</option><option value="Color Picker">Color Picker</option><option value="File">File</option><option value="Page Selector">Page Selector</option><option value="Custom">Custom</option></select></div><div class="attributeField extend" style="display: none;"><label for="multiple'+rowID+'">Mulitple</label><input type="checkbox" name="data[_isMultiple]['+rowID+']" ref="'+rowID+'" id="multiple'+rowID+'" value="1" class="multi-check"></div><div class="attributeField extend" style="display: none;"><span style="display:none;" id="group'+rowID+'"><label for="select_'+rowID+'">Loop Set</label><select name="data[_group]['+rowID+']" class="groupSelect" id="select_multiple'+rowID+'"></select></span></div><div style="display:block" id="tabgroup'+rowID+'" class="attributeField"><label for="tabgroupfield'+rowID+'">Group</label><input type="text" style="width: 100px;" name="data[_tabgroup]['+rowID+']" id="tabgroupfield'+rowID+'" value="General Settings" ref="'+rowID+'" class="tabgroupbox"></div></div><div class="attribute-row-right"><div class="attributeField extend" style="display: none;"><label for="info'+rowID+'">Info</label><textarea name="data[_variableInfo]['+rowID+']" id="info'+rowID+'"></textarea></div><div class="attributeField"><label for="default'+rowID+'">Default</label><textarea name="data[_variableDefault]['+rowID+']" id="default'+rowID+'"></textarea></div></div><div class="clear"></div><div class="attributeFooter"><a onclick="jQuery(this).slideUp(130,function(){jQuery(this).parent().find(\'.confirm\').slideDown(130)}); return false;" href="#" class="removal cbutton cbutton-sml"><i class="icon-remove"></i> Remove Attribute&nbsp;</a><a style="display:none;" onclick="jQuery(\'#'+rowID+'\').slideUp(130, function(){jQuery(this).remove()}); return false;" href="#" class="confirm cbutton cbutton-sml"><i class="icon-check"></i> Confirm?&nbsp;</a> <a style="display:none;" onclick="jQuery(this).parent().find(\'.confirm\').slideUp(130, function(){jQuery(this).parent().find(\'.removal\').slideDown(130)}); return false;" href="#" class="confirm cbutton cbutton-sml"><i class="icon-share-alt"></i> Cancel&nbsp;</a></div></div>').hide().prependTo('#variablePane').slideDown(130);
        jQuery('<div class="attributeItem" id="'+rowID+'" style=""><div class="attribute-row-left"><div class="attributeField"><label for="label'+rowID+'">Label</label><input type="text" style="width: 100px;" name="data[_label]['+rowID+']" id="name'+rowID+'" value="'+rowID+'" ref="'+rowID+'" class="labelbox"></div><div class="attributeField tiny"><label for="slug'+rowID+'">Slug</label><input type="text" style="width: 100px;" name="data[_variable]['+rowID+']" id="slug'+rowID+'" value="'+rowID+'" ref="'+rowID+'" class="slugbox"></div><div class="attributeField"><label for="type'+rowID+'">Type</label><select style="width: 100px;" id="type'+rowID+'" name="data[_type]['+rowID+']"><option selected="selected" value="Text Field">Text Field</option><option value="Text Box">Text Box</option><option value="Dropdown">Dropdown</option><option value="Checkbox">Checkbox</option><option value="Radio">Radio</option><option value="Color Picker">Color Picker</option><option value="File">File</option><option value="Page Selector">Page Selector</option></select></div><div class="attributeField extend" style="display: none;"><label for="multiple'+rowID+'">Mulitple</label><input type="checkbox" name="data[_isMultiple]['+rowID+']" ref="'+rowID+'" id="multiple'+rowID+'" value="1" class="multi-check"></div><div class="attributeField extend" style="display: none;"><span style="display:none;" id="group'+rowID+'"><label for="select_'+rowID+'">Loop Set</label><select name="data[_group]['+rowID+']" class="groupSelect" id="select_multiple'+rowID+'"></select></span></div><div style="display:block" id="tabgroup'+rowID+'" class="attributeField"><label for="tabgroupfield'+rowID+'">Group</label><input type="text" style="width: 100px;" name="data[_tabgroup]['+rowID+']" id="tabgroupfield'+rowID+'" value="General Settings" ref="'+rowID+'" class="tabgroupbox"></div></div><div class="attribute-row-right"><div class="attributeField extend" style="display: none;"><label for="info'+rowID+'">Info</label><textarea name="data[_variableInfo]['+rowID+']" id="info'+rowID+'"></textarea></div><div class="attributeField"><label for="default'+rowID+'">Default</label><textarea name="data[_variableDefault]['+rowID+']" id="default'+rowID+'"></textarea></div></div><div class="clear"></div><div class="attributeFooter"><a onclick="jQuery(this).slideUp(130,function(){jQuery(this).parent().find(\'.confirm\').slideDown(130)}); return false;" href="#" class="removal cbutton cbutton-sml"><i class="icon-remove"></i> Remove Attribute&nbsp;</a><a style="display:none;" onclick="jQuery(\'#'+rowID+'\').slideUp(130, function(){jQuery(this).remove()}); return false;" href="#" class="confirm cbutton cbutton-sml"><i class="icon-check"></i> Confirm?&nbsp;</a> <a style="display:none;" onclick="jQuery(this).parent().find(\'.confirm\').slideUp(130, function(){jQuery(this).parent().find(\'.removal\').slideDown(130)}); return false;" href="#" class="confirm cbutton cbutton-sml"><i class="icon-share-alt"></i> Cancel&nbsp;</a></div></div>').hide().prependTo('#variablePane').slideDown(130);
        
    }


jQuery('#variablePane').on('change','.slugbox', function(){
    var key = jQuery(this).attr('ref');
    jQuery('[value="'+key+'"]').html(this.value);
});
jQuery('#variablePane').on('change','.groupSelect', function(){
    
    var thatKey = jQuery(this).val();
    var thisKey = jQuery(this).find(':selected').attr('ref');
    
    jQuery('.attributeItem').each(function(){
        if(jQuery('#multiple'+this.id).attr('checked')){
            console.log(jQuery('#slug'+this.id).val()+' - has: '+jQuery('.groupSelect').find('option:selected[value="'+this.id+'"]').length);
            if(jQuery('.groupSelect').find('option:selected[value="'+this.id+'"]').length <= 0){
                if(jQuery('#tabgroupfield'+this.id).val() == 'General Settings'){
                    jQuery('#tabgroupfield'+this.id).val(jQuery('#slug'+this.id).val());
                }
                jQuery('#tabgroup'+this.id).hide().find('label').html('Group');
            }else{
                if(jQuery('#tabgroupfield'+this.id).val() == 'General Settings'){
                    jQuery('#tabgroupfield'+this.id).val(jQuery('#slug'+this.id).val());
                }            
                jQuery('#tabgroup'+this.id).show().find('label').html('Group label');
            }
        }else{
            jQuery('#tabgroup'+this.id).show().find('label').html('Group');            
        }
    })

    
});
jQuery('#variablePane').on('click','input:checkbox', function(){
    
    var key = jQuery(this).attr('ref');
    if(jQuery(this).attr('checked')){
       jQuery('#tabgroup'+key).hide();
       jQuery('#group'+key).show();
    }else{
       jQuery('#group'+key).hide();
       jQuery('#tabgroup'+key).show().find('label').html('Group');
    }
        
        jQuery('.groupSelect').each(function(){
            var item = this.id;
            if(jQuery(this).parent().parent().parent().find('.multi-check:checked').length > 0){
                jQuery('.multi-check').each(function(){
                    var ischecked = jQuery(this).attr('checked');
                    var val = jQuery(this).attr('ref');
                    var label = jQuery('#slug'+val).val();
                    var count = jQuery('#'+item+' option[value="'+val+'"]').length;
                    if(count < 1){
                        if(ischecked){
                            jQuery('#'+item).append('<option value="'+val+'">'+label+'</option>');
                        }
                    }else{
                        if(!ischecked){
                            jQuery('#'+item+' [value="'+val+'"]').remove();
                        }
                    }
                })
            }
        });
    jQuery('.attributeItem').each(function(){
        if(jQuery('#multiple'+this.id).attr('checked')){
            if(jQuery('.groupSelect').find('option:selected[value="'+this.id+'"]').length <= 0){
                if(jQuery('#tabgroupfield'+this.id).val() == 'General Settings'){
                    jQuery('#tabgroupfield'+this.id).val(jQuery('#slug'+this.id).val());
                }
                jQuery('#tabgroup'+this.id).hide().find('label').html('Group');
            }else{
                if(jQuery('#tabgroupfield'+this.id).val() == 'General Settings'){
                    jQuery('#tabgroupfield'+this.id).val(jQuery('#slug'+this.id).val());
                }            
                jQuery('#tabgroup'+this.id).show().find('label').html('Group label');
            }
        }   
    })        

})
</script>