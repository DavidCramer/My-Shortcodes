<?php
//global $wp_admin_bar;
global $footerOutput, $headerscripts, $javascript, $phpincludes, $contentPrefilters;

$shortcode = stripslashes_deep(strtolower($_GET['code']));
$used = msc_getUsedShortcodes($shortcode, array(), true, true);
$elements = get_option('CE_ELEMENTS');
foreach ($elements as $element => $options){
    if(!empty($options['shortcode'])){        
        foreach($used[2] as $currentKey=>$currentShortcode){
            if($options['shortcode'] == strtolower($used[2][$currentKey])){
                if(!empty($used[3][0])){
                    $atts[$currentKey] = shortcode_parse_atts($used[3][$currentKey]);
                }else{
                    $atts[$currentKey] = array();
                }
                $IDs[$currentKey] = $element;
                $shortcodes[$currentKey] = $options['shortcode'];
            }
        }
    }
}
if(!empty($IDs)){
    foreach($IDs as $currentKey=>$ID){
        $instance[$currentKey] = msc_getDefaultAtts($ID, $atts[$currentKey]);
        msc_processHeaders($ID, $instance[$currentKey]['atts']);
    }
    $outPutCode = msc_doShortcode($instance[0]['atts'], "Yes, if you make it look like an electrical fire. When you do things right, people won't be sure you've done anything at all. And why did 'I' have to take a cab? You've killed me! Oh, you've killed me!", $shortcodes[0]);

}

wp_enqueue_script('jquery');
wp_enqueue_style('msc-preview', MYSHORTCODES_URL.'styles/preview.css');
wp_enqueue_style('msc-core', MYSHORTCODES_URL.'styles/core.css');
wp_enqueue_style('msc-panel', MYSHORTCODES_URL.'styles/panel.css');
wp_enqueue_style('msc-minicolors', MYSHORTCODES_URL.'styles/minicolors.css');
wp_enqueue_script('msc-minicolors', MYSHORTCODES_URL.'libs/js/minicolors.js');
wp_enqueue_script('msc-jsshell', MYSHORTCODES_URL.'libs/js/shell.js');


function get_current_screen(){
    $out = new stdClass();
    $out->base = false;
    return $out;
}
class WP_Admin_Bar_remove {       
    public function initialize() {
        remove_action( 'wp_head', 'wp_admin_bar_header' );
        remove_action( 'admin_head', 'wp_admin_bar_header' );
    }
    public function render() {
        return;
    }
}

    if(!empty($_GET['mode'])){
        $previewcolor = 'none';
        $previewbgcolor = get_option('CE_PREVIEWBGCOLOR');
        if(empty($previewbgcolor)){
            $previewbgcolor = '#FFFFFF';
        }
    }else{
        $previewcolor = get_option('CE_PREVIEWCOLOR');
        if(empty($previewcolor)){
            $previewcolor = '#FFFFFF';
        }
        $previewbgcolor = get_option('CE_PREVIEWBGCOLOR');
        if(empty($previewbgcolor)){
            $previewbgcolor = 'url("'.MYSHORTCODES_URL.'styles/images/previewbg.png")';
        }
    }


?>
<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
        <title>Shortcode Preview</title>
        <?php wp_head(); ?>



        <!-- Mine
        <link media="all" type="text/css" href="<?php echo get_admin_url(); ?>load-styles.php?c=1&dir=ltr&load=wp-admin,media" rel="stylesheet">
        <link id="colors-css" media="all" type="text/css" href="<?php echo get_admin_url(); ?>css/colors-fresh.css" rel="stylesheet">
        <link media="all" type="text/css" href="<?php echo MYSHORTCODES_URL; ?>styles/core.css" rel="stylesheet">
        <link media="all" type="text/css" href="<?php echo MYSHORTCODES_URL; ?>styles/panel.css" rel="stylesheet">
        <link media="all" type="text/css" href="<?php echo MYSHORTCODES_URL; ?>styles/minicolors.css" rel="stylesheet">
        <script type='text/javascript' src='<?php echo get_admin_url(); ?>load-scripts.php?c=1&amp;load=jquery,utils'></script>
        <script type='text/javascript' src='<?php echo MYSHORTCODES_URL; ?>libs/js/minicolors.js'></script>-->
          
        <style type="text/css" media="screen">
      html { 
          background: <?php echo $previewbgcolor; ?>;
          height: auto !important;
      }
      
      .wrapper{
        background: none repeat scroll 0 0 <?php echo $previewcolor; ?>;
        <?php if(empty($_GET['mode'])){ ?>
        border-radius: 5px 5px 5px 5px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        text-align: center;
        width: 750px;
        margin: 0 auto;
        padding:20px 0 0;
        <?php } ?>
       }
       <?php if(!empty($_GET['debugmode'])){ ?>
       #-caldera-js-console--{background: none repeat scroll 0 0 #ff0000;bottom: 0;color: #FFFFFF !important;left: 0;overflow: auto;padding: 0 10px;position: fixed;right: 0;}
       #-caldera-js-console-- p{background-color: #333333;margin: 0 -10px;padding: 0 10px;}
       #-caldera-js-input--{display:none;}
       #-caldera-js-console-- p.error{background-color: #ff0000;margin: 0 -10px;padding: 0 10px;}
       <?php } ?>

        #previewfooter {
          clear:both;
          margin-top: 50px;
          border-top: 1px solid #E8EDEF;
          padding: 4px 0;
          color: #c4ced2;
        }
        #previewbox{
            margin: 30px 50px 46px 50px;
            <?php if(!empty($_GET['mode'])){ ?>
                margin-top: 0px;
                padding-top: 20px;
                padding-bottom: 20px;
            <?php } ?>
            text-align:left;
        }
        #title{
            color:#585858;
            margin:0 auto;
            width:550px;
            text-align: center;
            text-shadow: 0 -1px 0 rgba(0,0,0,0.4);
            font-size: 1.4em;
             font-weight: 300 !important;
        }
}
      </style>
    </head>
    <body class="previewCanvas">
        <?php if(empty($_GET['mode'])){ ?>
        <div id="title"><h1><input type="hidden" size="6" id="colorPicker" name="colourpicker" maxlength="7" autocomplete="off" value="<?php echo $previewcolor; ?>"><input type="hidden" size="6" id="colorPickerbg" name="colourpickerbg" maxlength="7" autocomplete="off" value="<?php echo $previewbgcolor; ?>"> Preview</h1></div>
        <?php }else{ ?>
        <p style="position: fixed; right: 5px; top: 5px;">
            <input type="hidden" size="6" id="colorPickerbg" name="colourpickerbg" maxlength="7" autocomplete="off" value="<?php echo $previewbgcolor; ?>">
        </p>
        <?php } ?>
        <div class="wrapper" <?php if(empty($_GET['mode'])){ ?>style="margin-top: 20px;"<?php } ?>>
            <div id="previewbox">
                <?php

                    if(!empty($outPutCode)){
                        echo $outPutCode;
                    }


                ?>
            </div>
            <?php if(empty($_GET['mode'])){ ?><div id="previewfooter">Preview does not account for theme used in final presentation.<?php } ?>

            

            </div>
        </div>
        <?php if(!empty($_GET['debugmode'])){ ?>
        <div id="-caldera-js-console--"></div>
        <textarea id="-caldera-js-input--"><?php
        echo $footerOutput;
        $footerOutput = false;
        ?></textarea>
        
        <script>

        function escapeHtml(unsafe) {
          return unsafe.toString()
              .replace(/&(?!amp;)/g, "&amp;")
              .replace(/<(?!lt;)/g, "&lt;")
              .replace(/>(?!gt;)/g, "&gt;")
              .replace(/"(?!quot;)/g, "&quot;")
              .replace(/'(?!#039;)/g, "&#039;");
        }
        function log(val){
            
            var console = document.getElementById('-caldera-js-console--');
            var log = document.createElement('p');
            log.innerHTML = escapeHtml(val);
            console.insertBefore(log, console.firstChild);

        }
        init();
        go();
        </script>
        <?php } ?>
        
        <?php wp_footer(); ?>
        <script>
	jQuery(document).ready(function(){
            jQuery("#colorPickerbg").miniColors({
                change: function(hex, rgb) {
                    jQuery('body').css('background', hex);
                    jQuery('html').css('background', hex);
                },
                close: function(hex, rgb) {

                    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
                    var data = {
                            action: 'msc_setPreviewBGColor',
                            color: hex
                    };
                    jQuery.post(ajaxurl, data, function(response) {

                    });

                }
            });
            jQuery("#colorPicker").miniColors({
                change: function(hex, rgb) {
                    jQuery('.wrapper').css('background', hex);
                },
                close: function(hex, rgb) {
                    
                    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
                    var data = {
                            action: 'msc_setPreviewColor',
                            color: hex
                    };                    
                    jQuery.post(ajaxurl, data, function(response) {
                       
                    });

                }
            });

            <?php if(!empty($_GET['mode'])){ ?>
//            setInterval(function(){
//                var docHeight = jQuery(document).height();
//                if(docHeight <= 489){
//                    docHeight = 490;
//                }
//                jQuery(parent.document.getElementById(frameElement.id)).height(docHeight);
//            }, 10);

            <?php } ?>
                
                

                
        })
        
        
        <?php
        //echo $footerOutput;
       // vardump($Element);
        ?></script>
    </body>
</html>