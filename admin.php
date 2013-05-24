<?php
global $shortcode_tags;
?>
<div class="wrap poststuff" id="msc_container">
<h2 style="display:none;"><!-- hack to force notices below here. not much in docs on how to do it properly. oh well. --></h2>
    <div id="header">
        <div class="title">
            <h2>My Shortcodes</h2><sub>V<?php echo MYSHORTCODES_VER; ?> </sub>
        </div>
        <div id="calderabanner">
            <br /><a class="button-primary" target="_blank" href="http://myshortcodes.cramer.co.za/pro-version/">Find out about the Pro Version</a>
        </div>
        <div class="clear"></div>
    </div>
    <div class="save_bar_tools">
        <span class="fbutton"><a href="?page=my-shortcodes&action=edit"><div id="addNewInterface" class="button"><span class="icon-plus-sign" style="margin-top:-1px;"></span> New Element</div></a></span>
        <span class="fbutton"><a href="#importer_screen" id="importer"><div class="button"><span class="icon-upload" style="margin-top:-1px;"></span> Import MSC File</div></a></span>
        &nbsp;&nbsp; <span class="fbutton"><a href="mailto:david@caldera.co.za?subject=My Shortcodes Feedback" id="feedback"><div class="button"><span class="icon-comment" style="margin-top:-1px;"></span> Send Feedback</div></a></span>
        <span class="tooltipControl"><label for="tooltipToggle">Disable Tooltips</label> <input type="checkbox" id="tooltipToggle" <?php if($settings['disableTooltips'] === 1){ echo 'checked="checked"'; } ?> /></span>
    </div>
    <div id="main">
        <div id="ce-nav">
            <ul>
                <?php
                
                msc_detectRogues();
                
                $Elements = get_option('CE_ELEMENTS');
                
                $toUpdate = array();
                                               
                $elementsFound = array();
                if(!empty($Elements)){
                    foreach($Elements as $ID=>$cfg){
                        //vardump($cfg, false);
                        if(!isset($cfg['removelinebreaks'])){
                            $toUpdate[$ID] = true;
                        }
                        if(!isset($cfg['codeType'])){
                            $toUpdate[$ID] = true;
                        }
                        if(!isset($cfg['elementType'])){
                            $toUpdate[$ID] = true;
                        }
                        if(!isset($cfg['state'])){
                            $toUpdate[$ID] = true;
                        }
                        if(!isset($cfg['shortcode'])){
                            $toUpdate[$ID] = true;
                        }
                        
                        $elementsFound[] = "'".$ID."'";
                    }
                }
                
                if(!empty($rogue)){
                    echo "<li class=\"current\">\n";
                    echo '<span class="cs-elementCount">'.count($rogue).'</span><a title="Rogue Elements" href="#_elementrogues">Rogue Elements</a>'."\n";
                    echo "</li>\n";                    
                }
                if(!empty($toUpdate)){
                    $class = '';
                    if(empty($rogue)){
                        $class = 'current';
                    }
                    echo "<li class=\"current\">\n";
                    echo '<span class="cs-elementCount">'.count($toUpdate).'</span><a title="Element Upgrades" href="#_elementupdates">Element Upgrades</a>'."\n";
                    echo "</li>\n";
                }

                $AlwaysLoads = get_option('CE_ALWAYSLOAD');
                $pages = array();
                if(empty($Elements)){
                    $Elements = array();
                }
                $allActive = array();
                foreach($Elements as $element=>$options){
                    if(!empty($options['category'])){
                        $pages[strtolower($options['category'])][] = $element;
                    }
                    if(!empty($options['state'])){
                        $allActive[] = $element;
                    }
                    if(!empty($options['childof'])){
                        $children[$options['childof']][] = $element;
                    }
                }
                
                if(!empty($allActive)){
                    $pages['__All Active____'] = $allActive;
                }
                ksort($pages);
                $elementindex = 1;
                if(empty($pages)){
                    echo '<li class="current">';
                        echo '<a title="Elements" href="#Elements">Shortcodes</a>';
                    echo '</li>';
                }
                $currentCat = false;
                if(!empty($_GET['cat'])){
                    $currentCat = $_GET['cat'];
                }
                if($currentCat == '__allactive____' && empty($allActive)){
                    $currentCat = false;
                }

                foreach($pages as $page=>$items){
                    $class = '';
                    $tabid = sanitize_key(strtolower($page));
                    if($elementindex === 1 && (empty($toUpdate) && empty($currentCat) && empty($rogue))){
                        $class = 'class="current"';
                    }
                    if(!empty($currentCat)){
                        if($currentCat == $tabid){
                            $class = 'class="current"';
                        }
                    }
                    

                ?>
                <li <?php echo $class; ?>>
                    <span class="cs-elementCount"><?php echo count($items); ?></span><a title="<?php echo sanitize_title(str_replace('__', '<strong>', str_replace('____', '</strong>', ucwords($page)))); ?>" href="#<?php echo $tabid; ?>"><?php echo str_replace('__', '<strong>', str_replace('____', '</strong>', ucwords($page))); ?></a>
                </li>
                <?php
                    $elementindex++;
                }
                ?>

            </ul>

        </div>

        <div id="content">
            <div style="display: none;" class="group" id="importer_screen">
                <h2>Import Element</h2>
                <form action="?page=my-shortcodes" method="post" enctype="multipart/form-data" id="importerForm">
                    <?php
                        echo wp_nonce_field('cs-import-shortcode');
                    ?>
                    File <input type="file" name="import" /><input class="button" type="submit" value="Import" />
                </form>
            </div>
            <?php

            if(!empty($toUpdate)){
                echo '<div class="group" id="_elementupdates">';
                echo '<h2>Elements Upgrades</h2>';
                echo '<div class="description">Things have changed since the last update. These elements need to be upgraded to retain compatibility.</div>';
                    //echo '<ul>';
                    foreach($toUpdate as $updateID=>$val){
                        echo '<span class="fbutton"><div id="upg_'.$updateID.'" class="elementUpgradeNodes" style="float:left; padding:3px; border-radius:4px; background:#ed0000; color:#fff;margin:3px;">'.$Elements[$updateID]['name'].'</div></span>';
                    }
                    //echo '</ul>';
                    ?>
                        <div class="exportbuttonbar clear">

                            <span id="upgradeElementsButton" class="fbutton" onclick="msc_upgradeElements();"><button type="button" class="button"><i class="icon-ok-sign"></i> Upgrade Elements</button></span>
                        </div>
            <?php
                echo "</div>\n";
            }


            ?>
        <?php
        $index = 1;
        if(empty($pages)){
            echo '<div class="group" id="Elements"><h2>Shortcodes</h2>Once you start creating shortcodes, they will be listed here and within their categories to the left.</div>';
        }
        if(!empty($_GET['el'])){
            $currentElement = $_GET['el'];
        }
        foreach($pages as $page=>$items){
            $tabid = sanitize_key(strtolower($page));
            $Show = 'none';
            if($index === 1 && (empty($toUpdate) && empty($currentCat))){
                $Show = 'block';
            }
            if(!empty($currentCat)){
                if($currentCat == $tabid){
                    $Show = 'block';
                }
            }
            $fromActive = '';
            if($tabid == '__allactive____'){
                $fromActive = '&from=active';
            }

        ?>
            <div style="display: <?php echo $Show; ?>;" class="group" id="<?php echo $tabid; ?>">
                <span class="fbutton exporter" style="float:right; padding: 6px 3px 3px 3px;">
                    <a href="#<?php echo sanitize_key(strtolower($page)); ?>">
                        <div class="button" id="addNewInterface">
                            <span style="margin-top:-1px;" class="icon-eye-open"></span> Show Export Config</div>
                    </a>
                </span>
                <span class="fbutton manager" style="float:right; padding: 6px 3px 3px 3px; display: none;">
                    <a href="#<?php echo sanitize_key(strtolower($page)); ?>">
                        <div class="button" id="addNewInterface">
                            <span style="margin-top:-1px;" class="icon-eye-close"></span> Hide Export Config</div>
                    </a>
                </span>

                <h2><?php echo str_replace('__', '', $page); ?></h2>
                <div class="catexport" style="display:none;">
                    <form action="?page=my-shortcodes" method="post" id="elementEditForm">
                        <ul class="tabs">
                            <li class="active"><a href="#pluginSettings">Plugin</a></li>
                            <li><a href="#elementSettings">Elements</a></li>
                        </ul>
                        <div class="pluginSettings settingTab">
                            <h2>Plugin Settings</h2>
                            <div class="description">This allows you to export this category of shortcodes as a standalone plugin.</div>
                            <?php

                            $pluginExport = get_option('_msp_'.sanitize_key($page));
                            
                            $user = wp_get_current_user();
                            if(empty($pluginExport)){
                                $pluginExport = array(
                                    '_pluginName' => str_replace('__', '', $page),
                                    '_pluginURI' => '',
                                    '_pluginDescription' => '',
                                    '_pluginAuthor' => $user->data->display_name,
                                    '_pluginVersion' => '1.00',
                                    '_pluginAuthorURI' => $user->data->user_url,
                                    '_includeWidget' => '2'
                                );
                                foreach($items as $Element){
                                    $pluginExport['_'.$Element.'_toExport'] = 1;
                                }
                            }

                            echo wp_nonce_field('mspro-exoport-set');
                            
                            echo msc_configOption('pluginSet', 'pluginSet', 'hidden', 'PluginSet', array('_pluginSet'=>sanitize_key($page)));
                            echo msc_configOption('pluginName', 'pluginName', 'textfield', 'Plugin Name', $pluginExport, 'Give the plugin a unique name');
                            echo msc_configOption('pluginURI', 'pluginURI', 'textfield', 'Plugin URL', $pluginExport, 'Set plugins website.');
                            echo msc_configOption('pluginDescription', 'pluginDescription', 'textfield', 'Plugin Description', $pluginExport, 'Give the plugin a description');
                            echo msc_configOption('pluginAuthor', 'pluginAuthor', 'textfield', 'Plugin Author', $pluginExport, 'Set the plugins author');
                            echo msc_configOption('pluginVersion', 'pluginVersion', 'textfield', 'Plugin Version', $pluginExport, 'Set the version of this plugin');
                            echo msc_configOption('pluginAuthorURI', 'pluginAuthorURI', 'textfield', 'Plugin Author URL', $pluginExport, 'Set the Authors website address.');
                            
                            ?>
                        </div>
                        <div class="elementSettings settingTab" style="display:none;">
                            <h2>Elements to Export</h2>
                            <div class="description">                                
                                <p>This allows you to select which elements are exported.</p>
                                <?php
                                //echo msc_configOption('_phpToLibrary', '_phpToLibrary', 'checkbox', 'Export PHP Tab as a functions file.', $pluginExport, $Elements[$Element]['description']);
                                ?>
                            </div>
                            <?php
                            foreach($items as $Element){
                                echo msc_configOption($Element.'_toExport', $Element.'_toExport', 'checkbox', $Elements[$Element]['name'], $pluginExport, $Elements[$Element]['description']);
                                //echo msc_configOption($Element.'_shortCodeOut', $Element.'_shortCodeOut', 'checkbox', 'Set element to export as shortcode', $pluginExport, 'This enables the element to be loaded as a shortcode via the shortcode inserter.');
                            }
                            ?>
                        </div>                       
                        <div class="exportbuttonbar">
                            <select name="exportType">
                                <option value="script" selected="selected">MSC Script</option>
                                <option value="script" disabled="disabled">WordPress Plugin (only available in Pro)</option>                                
                                <option value="script" disabled="disabled">PHP Include (only available in Pro)</option>
                            </select>
                            <span class="fbutton"><button type="submit" value="plugin" class="button"><i class="icon-download-alt"></i> Export</button></span>                            
                            <a class="button-primary" target="_blank" href="http://myshortcodes.cramer.co.za/pro-version/">Find out about the Pro Version</a>
                        </div>
                    </form>
                </div>

                <div class="catbody">
                <?php
                $showpane = true;
                if(!empty($_GET['exporterror'])){
                    if($_GET['exporterror'] == sanitize_key(strtolower($page))){
                        echo '<div class="alert alert-error" onclick="jQuery(this).fadeOut();" style="cursor:pointer;">You did not select any elements to export.</div>';
                        $showpane = false;
                    }
                }
                if(!empty($Elements)){
                
                foreach($items as $Element){
                
                    $Options = $Elements[$Element];
                    $ShortCode = 'celement id='.$Element;
                    if(!empty($Options['shortcode'])){
                        $ShortCode = $Options['shortcode'];
                    }
                    if(empty($Options['description'])){
                        $Options['description'] = '';
                    }
                    $activeClass= '';
                    if(!empty($Options['state'])){
                        $activeClass= 'active';
                    }

                    $icon = 'shortcode';
                    $toolTipType = 'Shortcode';
                    if(!empty($Options['elementType'])){
                        switch($Options['elementType']){

                            case '1':
                                $icon = 'shortcode';
                                $toolTipType = 'Shortcode';
                                break;
                            case '2':
                                $icon = 'iwidget';
                                $toolTipType = 'Widget';
                                break;
                            case '3':
                                $icon = 'hybrid';
                                $toolTipType = 'Hybrid (Widget & Shortcode)';
                                break;
                            case '4':
                                $icon = 'alwaysload';
                                $toolTipType = 'Always Load';
                                break;
                            case '5':
                                $icon = 'code';
                                $toolTipType = 'Code';
                                break;

                        }
                    }
                    $isError = '';
                    $isLast = '';
                    $errorTitle = '';
                    $errorEnable = '';
                    if(!empty($shortcode_tags[strtolower($Options['shortcode'])]) && empty($Options['state'])){
                        if($shortcode_tags[strtolower($Options['shortcode'])] != 'msc_doShortcode'){
                            //echo $shortcode_tags[strtolower($Options['shortcode'])];
                            $isError = 'errorDetected';
                            $errorTitle = 'Shortcode is in use. You\'ll need to disable the plugin with the existing shortcode or change the element slug'; ;
                            $errorEnable = 'data-animation="true"';
                        }
                    }
                    if(!empty($currentElement)){
                        if($currentElement == $Element){
                            $isLast = 'lastEdited';
                        }
                    }
                    //switch()
                ?>
                <div id="element_<?php echo $Element; ?>">
                <div class="cs-elementItem elementMain <?php echo $isError.' '.$isLast; ?>" title="<?php echo $errorTitle; ?>">
                    <div class="cs-elementIcon <?php echo $icon; ?>" rel="tooltip" title="<?php echo $toolTipType; ?>" data-placement="left"></div>
                    <div class="cs-elementInfoPanel">
                        <?php echo $Options['name']; ?>
                        <div class="cs-elementInfoPanel description"><?php echo $Options['description']; ?></div>
                    </div>
                    <div class="cs-elementInfoPanel mid">
                        <span class="fbutton"><span class="button infoTrigger" rel="<?php echo $Element; ?>" rel="tooltip" title="Show Details" data-placement="left"><i class="icon-cog"></i></span></span>
                        <?php
                        if(!empty($isError)){
                        ?>
                        <span class="fbutton"><a><span rel="tooltip" title="Cannot activate as there is an error." class="button disabled <?php echo $activeClass; ?>" data-placement="right" <?php echo $errorEnable; ?>><span class="icon-ok-circle" style="cursor:pointer;"></span></span></a></span>
                        <?php
                        }else{
                        ?>
                        <span class="fbutton"><a href="?page=my-shortcodes&action=activate&element=<?php echo $Element.$fromActive; ?>"><span rel="tooltip" title="<?php if(!empty($activeClass)){ echo 'Deactivate'; }else{ echo 'Activate'; }; ?>" class="button <?php echo $activeClass; ?>" data-placement="right"><span class="icon-ok-circle" style="cursor:pointer;"></span></span></a></span>
                        <?php
                        }
                        ?>
                    </div>
                    <div id="" class="cs-elementInfoPanel last buttonbar buttons_<?php echo $Element; ?>" style="display:block;">                        
                        
                        <span class="fbutton"><a href="?page=my-shortcodes&action=edit&element=<?php echo $Element; ?>"><div class="button" rel="tooltip" title="Edit Element" data-placement="left"><span class="icon-edit"></span></div></a></span>
                        <span class="fbutton"><a href="?page=my-shortcodes&action=dup&element=<?php echo $Element; ?>"><div class="button" rel="tooltip" title="Duplicate Element" data-placement="left"><span class="icon-chevron-down"></span></div></a></span>
                        <span class="fbutton"><a href="#" class="confirm" rel="<?php echo $Element; ?>" onclick="return false;"><div class="button" rel="tooltip" title="Delete Element" data-placement="right"><span class="icon-remove-sign"></span></div></a></span>
                    </div>
                    <div id="confirm_<?php echo $Element; ?>" class="cs-elementInfoPanel last buttons_<?php echo $Element; ?>" style="display:none;">
                        <div class="infoDelete">Delete Element</div>
                        <span class="fbutton"><a href="#" onclick="msc_deleteElement('<?php echo $Element; ?>'); return false;"><div class="button" rel="tooltip" title="Confirm Delete" data-placement="left"><span class="icon-ok"></span></div></a></span>
                        <span class="fbutton"><a href="#" onclick="return false;" class="confirm" rel="<?php echo $Element; ?>"><div class="button"><span class="icon-share-alt"></span> Cancel</div></a></span>

                    </div>
                </div>
                <?php
                    

                    $example = '['.$ShortCode.' ';
       


                ?>
                    <div class="cs-infopanel cs-elementItem options_<?php echo $Element; ?>">
                    <?php

                    if($Elements[$Element]['elementType'] == 1 || $Elements[$Element]['elementType'] == 3){

                    ?>

                        <h2>Shortcode</h2>
                        <table width="100%" class="widefat">
                            <thead>
                                <tr>
                                    <th width="125">Attribute</th>
                                    <th width="125">Default</th>
                                    <th width="250">Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if(!empty($Options['variables']['names'])){
                                    foreach($Options['variables']['names'] as $Key=>$Variable){

                                        $example .= $Variable.'="'.$Options['variables']['defaults'][$Key].'" ';

                                    ?>
                                    <tr>
                                        <td width="125"><?php echo $Variable; ?></td>
                                        <td width="125"><?php echo $Options['variables']['defaults'][$Key]; ?></td>
                                        <td width="250"><?php echo $Options['variables']['info'][$Key]; ?></td>
                                    </tr>
                                    <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                            if(empty($Options['variables']['names'])){
                                echo 'No Attributes set';
                            }
                            echo '<h2>Default Usage</h2>';
                            echo '['.$ShortCode.']';
                            if($Options['codeType'] == 2){
                                echo ' content [/'.$ShortCode.']';
                            }
                            if(!empty($Options['variables']['names'])){
                                echo '<h2>Full usage with defaults</h2>';
                                echo $example.']';
                                if($Options['codeType'] == 2){
                                    echo ' content [/'.$ShortCode.']';
                                }
                            }

                    }
                    if($Elements[$Element]['elementType'] == 2){
                                echo '<h2>Widget</h2>';
                                echo '<p>Once Avtivated, go to Apperance-><a href="widgets.php">Widgets</a> and select drag the My Shortcodes Pro widget into the side bar of choice.<p>';
                                echo '<p>Once in your side bar, select the category "<strong>'.$Elements[$Element]['category'].'</strong>" and the Element "<strong>'.$Elements[$Element]['name'].'</strong>" and click "Load Element".<p>';
                                echo '<p>You can now configure the element how you please.<p>';
                    }

                        ?>
                    </div>
                    </div>
                <?php
                }}else{
                    echo 'You have no elements';
                }
                ?>

            </div>
        </div>
            <?php
            if(!empty($children[$Element])){
                foreach($children[$Element] as $childElement){
                    $Options = $Elements[$childElement];












?>
                <div id="element_<?php echo $Element; ?>" class="child">
                <div class="cs-elementItem">
                    <div class="cs-elementInfoPanel">
                        <?php echo $Options['name']; ?>
                        <div class="cs-elementInfoPanel description"><?php echo $Options['description']; ?></div>
                    </div>
                    <div class="cs-elementInfoPanel mid">Shortcode <?php if(!empty($Options['variables'])){ ?><span class="infoTrigger" rel="<?php echo $Element; ?>">Attributes</span><?php } ?>
                        <div class="cs-elementInfoPanel description">[<?php echo $ShortCode; ?>]</div>
                    </div>
                    <div id="" class="cs-elementInfoPanel last buttonbar buttons_<?php echo $Element; ?>" style="display:block;">
                        <span class="fbutton"><a href="?page=my-shortcodes&action=edit&element=<?php echo $Element; ?>"><div class="button"><span class="icon-edit"></span></div></a></span>
                        <span class="fbutton"><a href="?page=my-shortcodes&action=edit&childof=<?php echo $Element; ?>"><div class="button"><span class="icon-indent-left"></span></div></a></span>
                        <span class="fbutton"><a href="#" class="confirm" rel="<?php echo $Element; ?>" onclick="return false;"><div class="button"><span class="icon-remove-sign"></span></div></a></span>
                    </div>
                    <div id="confirm_<?php echo $Element; ?>" class="cs-elementInfoPanel last buttons_<?php echo $Element; ?>" style="display:none;">
                        <div class="infoDelete">Delete Element</div>
                        <span class="fbutton"><a href="#" onclick="msc_deleteElement('<?php echo $Element; ?>'); return false;"><div class="button"><span class="icon-ok"></span></div></a></span>
                        <span class="fbutton"><a href="#" onclick="return false;" class="confirm" rel="<?php echo $Element; ?>"><div class="button"><span class="icon-share-alt"></span> Cancel</div></a></span>

                    </div>
                </div>
                <?php
                    if(!empty($Options['variables'])){

                    $example = '['.$ShortCode.' ';

                ?>
                    <div class="cs-infopanel cs-elementItem" id="options_<?php echo $Element; ?>">
                        <h2>Attributes</h2>
                        <table width="100%" class="widefat">
                            <thead>
                                <tr>
                                    <th width="125">Attribute</th>
                                    <th width="125">Default</th>
                                    <th width="250">Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach($Options['variables']['names'] as $Key=>$Variable){

                                    $example .= $Variable.'="'.$Options['variables']['defaults'][$Key].'" ';

                                ?>
                                <tr>
                                    <td width="125"><?php echo $Variable; ?></td>
                                    <td width="125"><?php echo $Options['variables']['defaults'][$Key]; ?></td>
                                    <td width="250"><?php echo $Options['variables']['info'][$Key]; ?></td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php

                            echo '<h2>Default Usage</h2>';
                            echo '['.$ShortCode.']';
                            if($Options['codeType'] == 2){
                                echo ' content [/'.$ShortCode.']';
                            }
                            echo '<h2>Full usage with defaults</h2>';
                            echo $example.']';
                            if($Options['codeType'] == 2){
                                echo ' content [/'.$ShortCode.']';
                            }

                        ?>
                    </div>
                <?php
                    }
                ?>
                    </div>

<?php
























                }

            }

            $index++;
        }
            ?>



        </div>
        <div class="clear"></div>

    </div>
    <div class="save_bar_top" style="padding:10px; height: 15px;">

    </div>

    <div style="clear:both;"></div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){

        jQuery('.confirm').click(function(){
            var ele = jQuery(this).attr('rel');
            jQuery('.buttons_'+ele).slideToggle();
        });
        jQuery('.infoTrigger').click(function(){
            jQuery(this).toggleClass('active');
            jQuery('.options_'+jQuery(this).attr('rel')).slideToggle();
            if(jQuery(this).hasClass('active')){
                jQuery(this).tooltip('hide').attr('data-original-title', 'Hide Details').tooltip('fixtitle').tooltip('show');
            }else{
                jQuery(this).attr('data-original-title', 'Show Details').tooltip('fixtitle').tooltip('show');
            }
        });
        jQuery('#ce-nav li a').click(function(){
            jQuery('#ce-nav li').removeClass('current');
            jQuery('.group').hide();
            jQuery(''+jQuery(this).attr('href')+'').show();
            jQuery(this).parent().addClass('current');
            return false;
        });
        jQuery('#importer').click(function(){
            jQuery('#ce-nav li').removeClass('current');
            jQuery('.group').hide();
            jQuery(''+jQuery(this).attr('href')+'').show();
            jQuery(this).parent().addClass('current');
            return false;
        });
        jQuery('#explain').click(function(){
            jQuery('#ce-nav li').removeClass('current');
            jQuery('.group').hide();
            jQuery(''+jQuery(this).attr('href')+'').show();
            jQuery(this).parent().addClass('current');
            return false;
        });

        jQuery('.exporter').click(function(){
            jQuery(this).hide();
            jQuery(this).parent().find('.manager').show();
            jQuery(this).parent().find('.catbody').slideToggle();
            jQuery(this).parent().find('.catexport').slideToggle();
        })
        jQuery('.manager').click(function(){
            jQuery(this).hide();
            jQuery(this).parent().find('.exporter').show();
            jQuery(this).parent().find('.catbody').slideToggle();
            jQuery(this).parent().find('.catexport').slideToggle();
        })
        jQuery('.tabs li a').click(function(){
            jQuery(this).parent().parent().find('.active').removeClass('active');
            jQuery(this).parent().addClass('active');
            jQuery(this).parent().parent().parent().find('.settingTab').hide();
            jQuery('.'+jQuery(this).attr('href').substring(1)).show();
            return false;
        })

      if(window.location.hash){
        var hash = window.location.hash.substring(1);
        jQuery('.current').removeClass('current');

        var vals = hash.split('&');        

        jQuery('a[href="#'+vals[0]+'"]').parent().addClass('current');
        jQuery('#content .group').hide();
        jQuery('#'+vals[0]).show();
        jQuery('#element_'+vals[1]+' .cs-elementItem.elementMain').addClass('lastEdited');
        //jQuery('.lastEdited').tooltip({title: 'Last Edited', placement: 'top'});

        //alert (hash);
      }

        jQuery( ".cs-elementItem" ).draggable({
            cursor: "move",
            distance: 20,
            cursorAt: { top: 10, left: -10 },
            helper: function( event ) {
                    return jQuery( "<div class='cs-elementItem' style='height:20px; padding: 5px 10px; z-index:10000;'>Drag to new Category</div>" );
            }
        });
        jQuery( "#ce-nav li" ).droppable({
            drop: function( event, ui ) {
                if(jQuery(this).find('a').attr('title') == '__All Active____'){
                    alert('You can activate the element by clicking the activate button.');
                }else{
                    msc_moveElement(jQuery(ui.draggable).parent().attr('id'), jQuery(this).find('a').attr('title'));
                    var num = parseFloat(jQuery(this).find('.cs-elementCount').html())+1;
                    jQuery(this).find('.cs-elementCount').html(num);
                    var lastnum = parseFloat(jQuery(this).parent().find('[href="#'+jQuery(ui.draggable).parent().parent().parent().attr('id')+'"]').prev().html())-1;
                    if(lastnum === 0){
                        jQuery(this).parent().find('[href="#'+jQuery(ui.draggable).parent().parent().parent().attr('id')+'"]').parent().slideUp();
                    }else{
                        jQuery(this).parent().find('[href="#'+jQuery(ui.draggable).parent().parent().parent().attr('id')+'"]').prev().html(lastnum);
                    }
                    jQuery(ui.draggable).parent().appendTo(jQuery(this).find('a').attr('href')+' .catbody');
                }
            }
        });
        <?php
        if(empty($settings['disableTooltips'])){
        ?>
        jQuery('.button, .cs-elementIcon').tooltip({animation: false});
        jQuery('.errorDetected').tooltip();
        <?php
        }
        ?>
        jQuery('#tooltipToggle').click(function(){
            var checkVal = jQuery(this).attr('checked');
            
            if(checkVal){
                jQuery('.button, .cs-elementIcon').tooltip('disable');
                jQuery('.errorDetected').tooltip('disable');
            }else{
                jQuery('.button, .cs-elementIcon').tooltip({animation: false});
                jQuery('.errorDetected').tooltip();
                jQuery('.button, .cs-elementIcon').tooltip('enable');
                jQuery('.errorDetected').tooltip('enable');
            }

            msc_setToolTips(checkVal);
        });
        
        

        
        <?php if(!empty($colorCode)){ ?>
            var els = jQuery('.codeColorElement');
            
            els.each(function(){
                //alert(this.id);
                CodeMirror.runMode(jQuery(this).html(), "text/x-php", this);
            });
        <?php } ?>        
        
    });
</script>