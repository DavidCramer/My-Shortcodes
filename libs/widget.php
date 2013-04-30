<?php


class msc_widget extends WP_Widget {
        function msc_widget() {
            $widget_ops = array( 'description' => 'Widgets & Hybrids');
            $control_ops = array( 'width' => 290, 'id_base' => 'ceelements');
            parent::WP_Widget('ceelements', 'Caldera', $widget_ops, $control_ops);
        }

        function addGroup($GroupID=false, $number=false, $instance = false){
                if($GroupID == false){
                    $GroupID = $_POST['group'];
                    $number = $_POST['number'];
                    $andDie = true;
                }
            $optionID = $instance['_element'];
            $Element = get_option($optionID);

            echo '<div class="widget-table rowGroup group'.$GroupID.'" id="group'.$GroupID.'" ref="'.$GroupID.'">';

            $first = true;
            foreach($Element['_group'] as $key=>$groupKey){
                if(!empty($Element['_isMultiple'][$key])){
                    if($groupKey == $GroupID){
                        $Default = false;
                        
                        if(!empty($instance[$Element['_variable'][$key].'_'.$number])){
                            $Default = $instance[$Element['_variable'][$key].'_'.$number];
                        }
                        $args = array(
                            'elementID' => $optionID,
                            'key' => $key,
                            'id' => $this->get_field_id($key.'_'.$number),
                            'name' => $this->get_field_name($Element['_variable'][$key].'_'.$number),
                            'default' => trim($Default),
                            'duplicate' => $first
                        );
                        echo msc_attsConfigFields($args);                        
                        $first = false;
                    }
                }
            }

            echo '</div>';
            if(!empty($andDie)){
                die;
            }

        }

    function form($instance) {            
            $Elements = get_option('CE_ELEMENTS');
            if(empty($Elements)){
                echo 'You don\'t have any elements.';
                return;
            }
            
                $cats = array();
                $eles = array();
                foreach($Elements as $ID=>$Options){
                    
                    if(!empty($Options['state']) && $Options['elementType'] == 2){
                        $Cat = strtolower($Options['category']);
                        $cats[$Cat] = $Cat;
                        $eles[$Cat][$ID] = strtolower($Options['name']);
                    }
                }
                if(empty($cats)){
                    echo 'You don\'t have any active Widget or Hybrid elements.';
                    return;
                }
                if(!empty($instance['_category']) && !empty($instance['_element'])){
                    $Element = get_option($instance['_element']);
                    if($Element['_elementType'] != 2){
                        unset($instance['_category']);
                        unset($instance['_element']);
                    }
                }
            if(!empty($instance['_category']) && !empty($instance['_element'])){
                echo "<div id=\"".$this->get_field_id('_elementSelector')."\" class=\"elementSelector\">\n";
            }

                echo "<p>\n";
                echo "<label for=\"".$this->get_field_id('_category')."\">Category:</label>\n";
                echo "<select class=\"widefat msc-cat-select\" id=\"".$this->get_field_id('_category')."\" ref=\"".$this->get_field_name('_element')."|".$this->get_field_id('_element')."\" name=\"".$this->get_field_name('_category')."\">\n";
                echo "<option value=\"\">Select Category</option>\n";
                    foreach($cats as $Cat){
                        $sel = "";
                        if(!empty($instance['_category'])){
                            if($instance['_category'] == $Cat){
                                $sel = 'selected="selected"';
                            }
                        }
                        echo "<option value=\"".$Cat."\" ".$sel.">".ucwords($Cat)."</option>\n";
                    }
                echo "</select>\n";
                echo "</p>\n";
                echo "<p id=\"ele".$this->get_field_id('_category')."\" class=\"elementBox\">\n";
                    if(!empty($instance['_category'])){
                        echo "<label for=\"".$this->get_field_id('_element')."\">Element:</label>\n";
                        echo "<select class=\"widefat msc-ele-select\" id=\"".$this->get_field_id('_element')."\" name=\"".$this->get_field_name('_element')."\">\n";
                        echo "<option value=\"\">Select Element</option>\n";
                            foreach($eles[$instance['_category']] as $ID=>$Ele){
                                $sel = "";
                                if(!empty($instance['_element'])){
                                    if($instance['_element'] == $ID){
                                        $sel = 'selected="selected"';
                                    }
                                }
                                echo "<option value=\"".$ID."\" ".$sel.">".ucwords($Ele)."</option>\n";
                            }
                        echo "</select>\n";
                        echo "<span class=\"fbutton\"><input type=\"submit\" class=\"widget-control-save button loadElementControl\" value=\"Load Element\" /></span>";
                    }                    
                echo "</p>\n";
            if(!empty($instance['_category']) && !empty($instance['_element'])){
                echo "</div>\n";
            echo '<div class="hide-if-no-js show-element-selector">
			<a class="show-elements-tab" href="#'.$this->get_field_id('_elementSelector').'">Show Element Selection</a>
			</div>';

            }
            if(empty($instance['_category']) || empty($instance['_element'])){
                return;
            }
            echo "<div id=\"form_".$this->get_field_id('_element')."\" class=\"clear scrollbox\">\n";
            echo '<h2 class="widgeth2">'.ucwords($eles[$instance['_category']][$instance['_element']]).'</h2>';
            $optionID = $instance['_element'];
            $Element = get_option($optionID);
            if(($Element['_elementType'] == 2 || $Element['_elementType'] == 3) && $Element['_widgetTitle'] == 2){
                $titleText = '';
                if(!empty($instance['_title'])){
                    $titleText = $instance['_title'];
                }
                echo '<p><label>Title<input type="text" value="'.$titleText.'" name="'.$this->get_field_name('_title').'" id="'.$this->get_field_id('_name').'" class="widefat"></label></p>';

            }
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

                //if(empty($instance)){
                    
                    if(!empty($Element['_variable'])){
                        foreach($Element['_variable'] as $key=>$var){
                            if(!empty($Element['_isMultiple'][$key])){
                                if($Element['_isMultiple'][$key]){
                                    $var = $var.'_1';
                                }
                            }
                            if(!isset($instance[$var])){
                                $instance[$var] = $Element['_variableDefault'][$key];
                            }

                        }
                    }
                    //update_option($optionID.'_cfg', $instance);
                //}

                if(empty($Element['_variable']) && $Element['_widgetWrap'] == 1 && $Element['_widgetTitle'] == 1){
                        echo '<p>No configuration nessasary for this plugin, just enjoy it!</p>';
                }else{
                    
                    foreach($Groups as $GroupName=>$vars){
                        $GroupID = sanitize_key($GroupName);
                        if(!empty($Element['_tabgroup'][$GroupID])){
                            $GroupName = ucwords($Element['_tabgroup'][$GroupID]);
                        }
                      
                        
                        echo '<div class="widget-table" id="header'.$GroupID.'">';
                        echo '<h2>'.$GroupName.'</h2>';
                        echo '</div>';
                        unset($isfirst);
                        echo '<div class="widget-table rowGroup group'.$GroupID.'" id="group'.$GroupID.'" ref="'.$GroupID.'">';

                        foreach($vars as $key=>$var){
                            if(!empty($Element['_isMultiple'][$key])){
                                if(empty($instance[$var.'_1'])){
                                    $instance[$var.'_1'] = $Element['_variableDefault'][$key];
                                }
                                $args = array(
                                    'elementID' => $optionID,
                                    'key' => $key,
                                    'id' => $this->get_field_id($key.'_1'),
                                    'name' => $this->get_field_name($var.'_1'),
                                    'default' => $instance[$var.'_1'],
                                    'duplicate' => false
                                );
                            }else{
                                $args = array(
                                    'elementID' => $optionID,
                                    'key' => $key,
                                    'id' => $this->get_field_id($key),
                                    'name' => $this->get_field_name($var),
                                    'default' => $instance[$var],
                                    'duplicate' => false
                                );                                
                            }
                            echo msc_attsConfigFields($args);
                        }

                        echo '</div>';

                        $run = true;
                        $index = 2;
                        while($run){
                            if(empty($GroupID) || empty($Element['_variable'][$GroupID])){
                                break;
                            }
                            if(!empty($instance[$Element['_variable'][$GroupID].'_'.$index])){
                                echo $this->addGroup($GroupID, $index, $instance);
                            }else{
                                $run = false;
                            }
                            $index++;
                        }



                     if(!empty($Element['_variable'][$GroupID])){
                        echo '<div class="toolrow" id="tool'.$GroupID.'" ref="'.$this->get_field_name('__addGroup__').'"><input type="submit" class="widget-control-save button addRow" value="Add '.$GroupName.'" ref="'.$GroupID.'"></div>';
                     }
                    }

                }                
                if(($Element['_elementType'] == 2 || $Element['_elementType'] == 3) && $Element['_shortcodeType'] == 2){
                    $contentText = '';
                    if(!empty($instance['_content'])){
                        $contentText = $instance['_content'];
                    }

                    echo '<label>Content<textarea name="'.$this->get_field_name('_content').'" id="'.$this->get_field_id('_content').'" cols="20" rows="16" class="widefat">'.$contentText.'</textarea></label>';
                }
                echo "</div>\n";
        }
    function update($new_instance, $old_instance) {
            $out_instance = array();
            foreach($new_instance as $name=>$values){
                if(is_array($values)){
                    foreach($values as $key=>$value){
                        $out_instance[$name.'_'.($key+1)] = $value;
                    }
                }else{
                    $out_instance[$name] = $values;
                }
            }
            $new_instance = $out_instance;
            if(!empty($new_instance['__addGroup__'])){
                $optionID = $new_instance['_element'];
                $Element = get_option($optionID);

                foreach($Element['_group'] as $Key=>$Group){
                    if($Group == $new_instance['__addGroup__']){
                        $index = 1;
                        $run = true;
                        while($run){
                            if(empty($new_instance[$Element['_variable'][$Key].'_'.$index])){
                                $new_instance[$Element['_variable'][$Key].'_'.$index] = ' ';
                                $run = false;
                            }
                            $index++;
                        }
                    }
                }
                unset($new_instance['__addGroup__']);
            }
            return $new_instance;
        }
    function widget($args, $instance) {

        $Element = get_option($instance['_element']);
        $Elements = get_option('CE_ELEMENTS');
        
        if(($Element['_elementType'] != 2 && $Element['_elementType'] != 3) || empty($Elements[$instance['_element']]['state'])){
            return;
        }
        
         extract( $args );
         if(!empty($instance['_content']) && $Element['_shortcodeType'] == '2'){
             $content = $instance['_content'];
             unset($instance['_content']);
         }else{
             $content = false;
         }
         foreach($instance as $key=>$val){
             $instance[$key] = strip_tags($val);
         }
         if($Element['_widgetWrap'] == '2'){
             echo $before_widget;
         }
         if($Element['_widgetTitle'] == '2'){
             echo $before_title.$instance['_title'].$after_title;
         }
         echo msc_doShortcode($instance, $content, $Element['_shortcode']);
         if($Element['_widgetWrap'] == '2'){
             echo $after_widget;
         }
        }
}

function msc_widget_init(){
    register_widget('msc_widget');
}


?>