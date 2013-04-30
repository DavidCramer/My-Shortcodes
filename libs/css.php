<div class="editor"><textarea name="data[_cssCode]" id="cssCode"><?php if(!empty($Element['_cssCode'])){ echo $Element['_cssCode']; } ;?></textarea></div>
<script type="text/javascript">
    
CodeMirror.defineMode("cssCode", function(config) {
  return CodeMirror.multiplexingMode(
    CodeMirror.getMode(config, "text/css"),
    {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
     mode: CodeMirror.getMode(config, "text/x-php"),
     delimStyle: "delimit"}
  );
});    
        
    
var csseditor = CodeMirror.fromTextArea(document.getElementById("cssCode"), {
    theme: "default",
    lineNumbers: true,
    matchBrackets: true,
    mode: "cssCode",
    indentUnit: 4,
    tabSize: 1,
    indentWithTabs: false,
    enterMode: "keep",
    tabMode: "shift",
    gutter: true,
    onBlur: function(){
        csseditor.save();
    }
});
</script>