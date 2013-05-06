<?php
    $Title = 'New Element';
    $Element = array();
    if(!empty($_GET['element'])){
        $Element = get_option($_GET['element']);
        $Title = '<span><strong>'.$Element['_name'].'</strong> - '.$Element['_shortcode'].'</span>';
    }else{
        $Element['_ID'] = strtoupper(uniqid('EL'));
        $Element['_shortcode'] = $Element['_ID'];
    }
    if(!isset($Element['__showpreview__'])){
        $Element['__showpreview__'] = 1;
    }
    // remove admin bar for preview
    //$user = wp_get_current_user();
    //$ismenu = get_user_meta($user->ID, 'show_admin_bar_front', true);
    //if($ismenu == true){
    //    update_user_meta($user->ID, 'show_admin_bar_front', false);
    //   $disableadminbarmessage = 'We disabled your admin bar for preview. You can enable it again in your profile settings.';
    //}
    
?>
    <form action="?page=my-shortcodes" method="post" id="editor-form">
    <?php wp_nonce_field('cs-edit-shortcode'); ?>
        <div class="header-nav">
            <div class="caldera-logo-icon"></div>
            <ul class="editor-section-tabs navigation-tabs">
                <li><a href="#php">PHP</a></li>
                <li><a href="#css">CSS</a></li>
                <li><a href="#html">HTML</a></li>
                <li><a href="#js">JS</a></li>
                <li class="divider-vertical"></li>
                <li><?php echo $Title; ?></li>
                <li class="divider-vertical"></li>
                <li class="fbutton"><button id="element-apply" type="button" class="button">Apply</button></li>
                <li class="fbutton"><button type="submit" class="button">Save</button></li>
                <li class="divider-vertical"></li><?php
                /*
                <li class="fbutton"><button id="preview-toggle" type="button" class="button <?php if(!empty($Element['__showpreview__'])){ echo 'active'; } ?>">Revisions</button></li>
                <li class="divider-vertical"></li> */
                ?>
                <li class="fbutton"><button id="preview-toggle" type="button" class="button <?php if(!empty($Element['__showpreview__'])){ echo 'active'; } ?>">Preview</button></li>
                <li class="divider-vertical"></li>
                <li><span id="saveIndicator"><progress>Saving</progress></span></li>
            </ul>
        </div>
        <div class="side-controls">
            <ul class="element-config-tabs navigation-tabs">
                <li class="active"><a class="control-settings-icon" href="#config" title="Settings"><span>Settings</span></a></li>
                <li><a class="control-attributes-icon active" href="#attributes" title="Attributes"><span>Attributes</span></a></li>
                <li><a class="control-libraries-icon" href="#libraries" title="Libraries"><span>Libraries</span></a></li>
                <li><a class="control-assets-icon" href="#assets" title="Assets"><span>Assets</span></a></li>
            </ul>
        </div>
        <div class="editor-pane" style="<?php if(empty($Element['__showpreview__'])){ echo 'right:0;'; }; ?>">            
            <div id="config" class="editor-tab active editor-setting editor-config">
                <div class="editor-tab-content">
                    <h3>Settings <small>Element settings and display options</small></h3>
                    <?php include MYSHORTCODES_PATH . 'libs/settings.php'; ?>
                </div>
            </div>
            <div id="attributes" class="editor-tab editor-setting editor-attributes">
                <div class="editor-tab-content">
                    <h3>Attributes <small>Element variables and attributes</small></h3>                    
                    <?php include MYSHORTCODES_PATH . 'libs/variables.php'; ?>
                </div>
            </div>
            <div id="libraries" class="editor-tab editor-setting editor-libraries">
                <div class="editor-tab-content">
                    <h3>Libraries <small>Scripts and styles to be included in the header</small></h3>                    
                    <?php include MYSHORTCODES_PATH . 'libs/libraries.php'; ?>
                </div>
            </div>
            <div id="assets" class="editor-tab editor-setting editor-assets">
                <div class="editor-tab-content">
                    <h3>Assets <small>Additional files and scripts to be used by your element.</small></h3>                    
                    <?php include MYSHORTCODES_PATH . 'libs/assets.php'; ?>
                </div>
            </div>
            <div id="php" class="editor-tab editor-code editor-php">
                <label for="code-php">PHP</label>
                <textarea id="code-php" name="data[_phpCode]"><?php if(!empty($Element['_phpCode'])){ echo htmlspecialchars($Element['_phpCode']); } ;?></textarea>
            </div>
            <div id="css" class="editor-tab editor-code editor-css">
                <label for="code-css">CSS</label>
                <textarea id="code-css" name="data[_cssCode]"><?php if(!empty($Element['_cssCode'])){ echo $Element['_cssCode']; } ;?></textarea>
            </div>
            <div id="html" class="editor-tab editor-code editor-html">
                <label for="code-html">HTML</label>
                <textarea id="code-html" name="data[_mainCode]"><?php if(!empty($Element['_mainCode'])){ echo htmlspecialchars($Element['_mainCode']); } ;?></textarea>
            </div>
            <div id="js" class="editor-tab editor-code editor-js">
                <label for="code-js">JavaScript</label>
                <textarea id="code-js" name="data[_javascriptCode]"><?php if(!empty($Element['_javascriptCode'])){ echo $Element['_javascriptCode']; } ;?></textarea>
            </div>            
        </div>
        <?php
        /*
        <div class="editor-revisions">
            <div class="editor-tab-content">
                <h3>Revisions</h3>
            </div>            
        </div>
         */
        ?>
        <div class="preview-pane editor-preview" style="<?php if(empty($Element['__showpreview__'])){ echo 'display:none;'; }; ?>">
            <label>Result</label>
            <?php include MYSHORTCODES_PATH . 'libs/preview.php'; ?>
        </div>
    </form>
        <script>
            /* Setup Editors */
            var mustache = function(stream, state) {
                  var ch;
                  if (stream.match("{{_")) {
                    while ((ch = stream.next()) != null)
                      if (ch == "_" && stream.next() == '}' && stream.peek(stream.pos+2) == '}') break;
                    stream.eat("}");
                    return "mustacheinternal";
                  }                  
                  if (stream.match("{{")) {
                    while ((ch = stream.next()) != null)
                      if (ch == "}" && stream.next() == "}") break;
                    stream.eat("}");
                    return "mustache";
                  }
                  if (stream.match("[once]") || stream.match("[/once]") || stream.match("[/loop]") || stream.match("[else]") || stream.match("[/if]")) {
                    return "command";
                  }
                  if (stream.match("[loop") || stream.match("[if")) {                  
                    while ((ch = stream.next()) != null)
                      if (ch == "]" && stream.next() != "]") break;
                    stream.eat("]");
                    return "command";
                  }
                  /*
                  if (stream.match("[[")) {
                    while ((ch = stream.next()) != null)
                      if (ch == "]" && stream.next() == "]") break;
                    stream.eat("]");
                    return "include";
                  }*/
                  while (stream.next() != null && 
                      !stream.match("{{", false) && 
                      !stream.match("[[", false) && 
                      !stream.match("{{_", false) && 
                      !stream.match("[once]", false) && 
                      !stream.match("[/once]", false) && 
                      !stream.match("[loop", false) && 
                      !stream.match("[/loop]", false) && 
                      !stream.match("[if", false) && 
                      !stream.match("[else]", false) && 
                      !stream.match("[/if]", false) ) {}
                  return null;
                };
                
            var phpeditor = CodeMirror.fromTextArea(document.getElementById("code-php"), {
              lineNumbers: true,
              matchBrackets: true,
              mode: "text/x-php",
              indentUnit: 4,
              indentWithTabs: true,
              enterMode: "keep",
              tabMode: "shift",
              lineWrapping: true,
              onBlur: function(){
                phpeditor.save();
              }
            });
            
            CodeMirror.defineMode("cssCode", function(config) {
              return CodeMirror.multiplexingMode(
                CodeMirror.getMode(config, "text/css"),
                {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
                 mode: CodeMirror.getMode(config, "text/x-php"),
                 delimStyle: "phptag"}
              );
            });
            CodeMirror.defineMode("cssMustache", function(config, parserConfig) {
              var mustacheOverlay = {
                token: mustache
              };
              return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "cssCode"), mustacheOverlay);
            });            
            var csseditor = CodeMirror.fromTextArea(document.getElementById("code-css"), {
              lineNumbers: true,
              matchBrackets: true,
              mode: "cssMustache",
              indentUnit: 4,
              indentWithTabs: true,
              enterMode: "keep",
              tabMode: "shift",
              lineWrapping: true,
              onBlur: function(){
                csseditor.save();
              }
            });
            
            CodeMirror.defineMode("mustache", function(config, parserConfig) {
              var mustacheOverlay = {
                token: mustache
              };
              return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "application/x-httpd-php"), mustacheOverlay);
            });
            var htmleditor = CodeMirror.fromTextArea(document.getElementById("code-html"), {
              lineNumbers: true,
              matchBrackets: true,
              mode: "mustache",
              indentUnit: 4,
              indentWithTabs: true,
              enterMode: "keep",
              tabMode: "shift",
              lineWrapping: true,
              onBlur: function(){
                htmleditor.save();
              }
            });
            
            CodeMirror.defineMode("jsCode", function(config) {
              return CodeMirror.multiplexingMode(
                CodeMirror.getMode(config, "text/javascript"),
                {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
                 mode: CodeMirror.getMode(config, "text/x-php"),
                 delimStyle: "phptag"}
              );
            });
            CodeMirror.defineMode("jsMustache", function(config, parserConfig) {
              var mustacheOverlay = {
                token: mustache
              };
              return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "jsCode"), mustacheOverlay);
            });            
            var jseditor = CodeMirror.fromTextArea(document.getElementById("code-js"), {
              lineNumbers: true,
              matchBrackets: true,
              mode: "jsMustache",
              indentUnit: 4,
              indentWithTabs: true,
              enterMode: "keep",
              tabMode: "shift",
              lineWrapping: true,
              onBlur: function(){
                jseditor.save();
              }
            });
            
            /* Setup Navigation Tabs */
            jQuery('.navigation-tabs li:not(.fbutton) a').click(function(e){
                e.preventDefault();
                var alltabs = jQuery('.navigation-tabs li');
                var clicked = jQuery(this);
                alltabs.removeClass('active');
                clicked.parent().addClass('active');
                var panel = jQuery(clicked.attr('href'));
                jQuery('.editor-tab').hide();
                panel.show();
                panel.find('textarea').focus();
                phpeditor.refresh();
                csseditor.refresh();
                htmleditor.refresh();
                jseditor.refresh();
            })
            /* Apply Element Changes & Reload Preview */
            jQuery('#element-apply').click(function(e){
                msc_reloadPreview('<?php echo $Element['_ID']; ?>');
            });
            
            /* Utility Functions */
            function randomUUID() {
                var s = [], itoh = '0123456789ABCDEF';
                for (var i = 0; i <6; i++) s[i] = Math.floor(Math.random()*0x10);
                return s.join('');
            }
            function msc_togglepreview(){

                if(jQuery('.editor-pane').css('right') == '0px'){
                    jQuery('.editor-pane').css({right: '50%'});                    
                    jQuery('#previewOutput').attr('src', '<?php echo site_url(); ?>?myshortcodeproinsert=preview&code=<?php echo $Element['_shortcode']; ?>');
                    jQuery('#setShowPreview').val('1');
                }else{
                    jQuery('.editor-pane').css({right: 0});
                    jQuery('#previewOutput').attr('src', '');
                    jQuery('#setShowPreview').val('0');
                }
                jQuery('.preview-pane').toggle();
                jQuery('#preview-toggle').toggleClass('active');
                phpeditor.refresh();
                csseditor.refresh();
                htmleditor.refresh();
                jseditor.refresh();                
            }
            
            /* ready calls */
            jQuery(document).ready(function(){
                jQuery('#preview-toggle').click(msc_togglepreview);
                jQuery( "#variablePane" ).sortable();
                jQuery( "#jslibraryPane" ).sortable();
                jQuery( "#assetPane" ).sortable();
                
                /* Bind ctr+s & cmd+s for saving*/
                
                jQuery(window).keypress(function(event) {
                    if (!(event.which == 115 && event.metaKey) && !(event.which == 19)) return true;
                    event.preventDefault();
                    htmleditor.save();
                    csseditor.save();
                    phpeditor.save();
                    jseditor.save();


                    msc_reloadPreview('<?php echo $Element['_ID']; ?>');
                    //msc_applyElement('<?php echo $Element['_ID']; ?>');
                    return false;
                });
                
            });
            
            
        </script>