<div class="editor"><textarea name="data[_javascriptCode]" id="javascriptCode"><?php if(!empty($Element['_javascriptCode'])){ echo $Element['_javascriptCode']; } ;?></textarea></div>
<script type="text/javascript">
    
CodeMirror.defineMode("jsCode", function(config) {
  return CodeMirror.multiplexingMode(
    CodeMirror.getMode(config, "text/javascript"),
    {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
     mode: CodeMirror.getMode(config, "text/x-php"),
     delimStyle: "delimit"}
  );
});    
    
    
var jseditor = CodeMirror.fromTextArea(document.getElementById("javascriptCode"), {
    theme: "default",
    autofocus: true,
    lineNumbers: true,
    matchBrackets: true,
    mode: "jsCode",
    indentUnit: 4,
    tabSize: 4,
    indentWithTabs: false,
    enterMode: "keep",
    tabMode: "shift",
    gutter: true,
    onBlur: function(){
        jseditor.save();
    }
});
</script>