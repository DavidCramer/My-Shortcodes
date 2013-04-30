<?php
    if(empty($Element['__showpreview__'])){
        $previewSRC = '';        
    }else{
        $previewSRC = site_url().'?myshortcodeproinsert=preview&code='.$Element['_shortcode'].'';
    }
?>
<iframe id="previewOutput" style="width: 100%;" src="<?php echo $previewSRC; ?>"></iframe>
<input type="hidden" id="setShowPreview" name="data[__showpreview__]" value="<?php echo $Element['__showpreview__']; ?>" />
<script type="text/javascript">

    function msc_reloadPreview(eid, nosave){

            var debugmode = '';
            
            var ifsrc = jQuery('#previewOutput').attr('src').split('&rdm');
            if(nosave){
                jQuery('#previewOutput').attr('src', ifsrc[0]+'&rdm='+Math.floor((Math.random()*10000)+1));
                return;
            }else{
                jQuery('#saveIndicator').fadeIn(200);
                var data = {
                        action: 'apply_element',
                        EID: eid,
                        formData: jQuery('#editor-form').serialize()
                };
                jQuery.post(ajaxurl, data, function(response) {
                    var newtitle = response;
                    if(jQuery('#name').val().length > 0){
                        newtitle = jQuery('#name').val();
                    }
                    jQuery('#elementTitle').html(newtitle);
                    if(jQuery('.preview-pane').is(':visible')){
                        jQuery('#previewOutput').attr('src', ifsrc[0]+'&rdm='+Math.floor((Math.random()*10000)+1));
                    }
                    jQuery('#saveIndicator').fadeOut(200);
                });
            }

    };


    
</script>