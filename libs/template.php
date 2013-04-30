<?php
//echo msc_configOption('_mainCode', 'mainCode', 'textarea', '', $Element);

?>
<div class="editor"><textarea name="data[_mainCode]" id="mainCode"><?php if(!empty($Element['_mainCode'])){ echo htmlspecialchars($Element['_mainCode']); } ;?></textarea></div>
<script type="text/javascript">
//var htmleditor = false
//jQuery('#tabid3').click(function(){
//    jQuery('#editorPane .tabs a').removeClass('active');
//    jQuery(this).addClass('active');
//    jQuery('#editorPane .area article').hide();
//    jQuery(jQuery(this).attr('href')).show();
//    if(htmleditor == false){
      var htmleditor = CodeMirror.fromTextArea(document.getElementById("mainCode"), {
            theme: "default",
            lineNumbers: true,
            matchBrackets: true,
            mode: "application/x-httpd-php",
            onBlur: function(){
                htmleditor.save();
            }
        });
//    }
//})




jQuery('#fullScreen').click(function(){
    if(jQuery('#fullScreenMode').length){
        jQuery('#fullScreenMode').remove();
    }else{
        jQuery("head").append(jQuery("<link rel='stylesheet' href='<?php echo MYSHORTCODES_URL."styles/fullscreen.css"; ?>' id='fullScreenMode' type='text/css' media='screen' />"));
        var panelWidth = jQuery(window).width()-20;
        jQuery('#ctb_9').width(panelWidth/2.2);
        jQuery('.group').css({'float': 'left'});
        jQuery('.group').width(panelWidth/2.2);
        jQuery('#tab_ctb_9').hide();
        jQuery('#ctb_9').show();
        jQuery('#ctb_9').css('border-left', '1px solid #f1f1f1');
        jQuery('#ctb_9').removeClass('group');
        jQuery('#msc_container #ce-nav ul').append('<li id="exitFSButton"><a href="#exitFS" title="Exit Fullscreen">Exit Fullscreen</a></li>').click(function(){
            jQuery('#fullScreenMode').remove();
            jQuery('#exitFSButton').remove();
            jQuery('.CodeMirror-scroll').height(600);
            jQuery('#ctb_9').css('border-left', '0');
            jQuery('#tab_ctb_9').show();
            jQuery('#ctb_9').hide();
            jQuery('#ctb_9').addClass('group');
        });
        jQuery('.CodeMirror-scroll').height(jQuery(window).height()-118);
    }
});

</script>