<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
        <title>Shortcode Builder</title>
        <link media="all" type="text/css" href="<?php echo get_admin_url(); ?>load-styles.php?c=1&dir=ltr&load=wp-admin,media" rel="stylesheet">        
        <link id="colors-css" media="all" type="text/css" href="<?php echo get_admin_url(); ?>css/colors-fresh.css" rel="stylesheet">
        <link media="all" type="text/css" href="<?php echo MYSHORTCODES_URL; ?>styles/core.css" rel="stylesheet">
        <link media="all" type="text/css" href="<?php echo MYSHORTCODES_URL; ?>styles/panel.css" rel="stylesheet">
        <link media="all" type="text/css" href="<?php echo MYSHORTCODES_URL; ?>styles/minicolors.css" rel="stylesheet">
        <script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
        <script type='text/javascript' src='<?php echo MYSHORTCODES_URL; ?>libs/js/minicolors.js'></script>
    </head>
    <body id="calderaShortcodeBuilder">
        <div class="toolbar">
            <?php 
            $Cats = msc_buildCategoriesDropdown();
            if(empty($Cats['error'])){
                echo "Category: ";
                echo $Cats['html'];
            
            ?>&nbsp;
            <div class="fbutton" style="float:right; margin-right:5px;">
                <div class="button" onclick="msc_sendCode();">
                    <i class="icon-plus" style="margin-top:-1px;"></i> Insert Shortcode
                </div>
            </div>
            Shortcode: <span id="element">Select a Category</span>
            <div class="fbutton" style="float:right; margin-right:5px;">
                <div class="button" onclick="msc_sendCodePreview();">
                    <i class="icon-eye-open" style="margin-top:-1px;"></i> Preview
                </div>
            </div>

            <?php
            }else{
                echo $Cats['message'];
            }
            ?>
        </div>
        <div id="medialibrary">
            <input type="hidden" id="forfield" />
            <div class="panel" id="mediaPanel">
            
        <?php


            $query_img_args = array(
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'posts_per_page' => -1,
                    );
            $query_img = new WP_Query( $query_img_args );
            
            $pages = ceil($query_img->post_count/12);


            $args = array(
                'post_type' => 'attachment',
                'numberposts' => 12,
                'offset' => 0,
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
        ?></div>
            <div class="mediatoolbar">
                <div class="fbutton">
                    <div class="button closemedia">
                        <i class="icon-remove-sign" style="margin-top:-1px;"></i> Close Media Library
                    </div>
                </div>
                <div  class="pagination">
                    <ul>
                        <?php
                        for($i = 1; $i<= $pages; $i++){
                            $class="";
                            if($i===1){
                                $class="current";
                            }
                            echo '<li><a class="'.$class.'" href="#'.$i.'">'.$i.'</a></li>';

                        }
                        ?>
                    </ul>
                </div>
            </div>


        </div>
        <div class="content contentShortcodeBuilder" id="contentShortcodeBuilder">

            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
            
        </div>
        <div class="footer">

        </div>

        <script>
            
            jQuery('#calderaShortcodeBuilder').on('click', '#ce-nav li a', function(){
                jQuery('#ce-nav li').removeClass('current');
                jQuery('.group').hide();
                jQuery(''+jQuery(this).attr('href')+'').show();
                jQuery(this).parent().addClass('current');
                return false;
            });            
            
            jQuery('#calderaShortcodeBuilder').on('click','.addRow', function(event){
                event.preventDefault();
                addGroup(jQuery(this).attr('href'), jQuery(this).attr('ref'));
            })
            function addGroup(id, EID){
                number = jQuery('.group'+id).length+1;

                var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
                var data = {
                        action: 'msc_addgroup',
                        group: id,
                        eid: EID,
                        number: number
                };
                jQuery.post(ajaxurl, data, function(response) {
                    jQuery('#tool'+id).before(response);
                });
            }
            function msc_sendCodePreview(){

                if(jQuery('#selectedelement').length > 0){
                    if(jQuery('#selectedelement').val() == ''){
                        return;
                    }
                    var shortcode = jQuery('#shortcodekey').val();
                    var output = '['+shortcode;
                    var ctype = '';
                    if(jQuery('#shortcodetype').val() == '2'){
                        var ctype = jQuery('#defaultContent').val()+'[/'+shortcode+']';
                    }
                    jQuery('.attrVal').each(function(){
                        output += ' '+this.id+'="'+this.value+'"';
                    });
                 window.open("<?php echo get_admin_url(); ?>post.php?myshortcodeproinsert=preview&code="+encodeURIComponent(output+']'+ctype));
                }
            }
            function msc_sendCode(){
                if(jQuery('#selectedelement').length > 0){
                    if(jQuery('#selectedelement').val() == ''){
                        return;
                    }
                    var shortcode = jQuery('#shortcodekey').val();
                    var output = '['+shortcode;
                    var ctype = '';
                    if(jQuery('#shortcodetype').val() == '2'){
                        var ctype = jQuery('#defaultContent').val()+'[/'+shortcode+']';
                    }
                    jQuery('#msc_container input,#msc_container select,#msc_container textarea').each(function(){
                        if(this.value.length > 0){
                            output += ' '+this.name+'="'+this.value+'"';
                        }
                    });
                    var win = window.dialogArguments || opener || parent || top;
                    win.send_to_editor(output+']'+ctype);
                }
            }
            function msc_loadCategory(){
                    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
                    var cat = jQuery('#category').val();
                    var data = {
                            action: 'load_elements',
                            category: cat
                    };
                    jQuery('#element').html('Loading...');
                    jQuery.post(ajaxurl, data, function(response) {
                        jQuery('#element').html(response);
                    });

            }
            function msc_loadElement(){
                    var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
                    var element = jQuery('#selectedelement').val();
                    var data = {
                            action: 'msc_load_elementConfig',
                            element: element
                    };
                    jQuery('#contentShortcodeBuilder').html('Loading Config...');
                    jQuery.post(ajaxurl, data, function(response) {
                        jQuery('#contentShortcodeBuilder').html(response);
                        jQuery('.miniColors-trigger-fake').remove();
                        jQuery('.minicolorPicker').miniColors();
                    });

            }

            jQuery('#calderaShortcodeBuilder').on('click','.remover', function(){

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

        jQuery('.closemedia').click(function(){            
            jQuery('#medialibrary').slideUp('50');
        });
        jQuery('.addFile')

       function msc_openMediaPanel(id){
            jQuery('#forfield').val(id);
            jQuery('#medialibrary').slideDown('50');
       };
       jQuery('#calderaShortcodeBuilder').on('click','.msc_uploader', function(){
            var id = this.id.replace('uploader_','');
            msc_openMediaPanel(id);
       })

        jQuery('#calderaShortcodeBuilder').on('click','.mediaElement a', function(e){
            e.preventDefault();
            jQuery('#'+jQuery('#forfield').val()).val(jQuery(this).attr('href'));
            jQuery('#medialibrary').slideUp();
            return false;
        })

        jQuery('.pagination a').click(function(){
            jQuery('.pagination a').removeClass('current');
            jQuery(this).addClass('current');
            loadMediaPage(jQuery(this).attr('href').substring(1));
        });

        function loadMediaPage(page){
            
            var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
            var data = {
                    action: 'loadmedia_page',
                    page: page
            };
            jQuery('#mediaPanel').html('<div class="loading">Loading</div>');
            jQuery.post(ajaxurl, data, function(response) {
                jQuery('#mediaPanel').html(response);
            });
        }

        </script>
    </body>
</html>