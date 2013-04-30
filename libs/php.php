<div class="editor"><textarea name="data[_phpCode]" id="phpCode"><?php if(!empty($Element['_phpCode'])){ echo htmlspecialchars($Element['_phpCode']); } ;?></textarea></div>
<script type="text/javascript">
var phpeditor = CodeMirror.fromTextArea(document.getElementById("phpCode"), {
    theme: "default",
    lineNumbers: true,
    matchBrackets: true,
    mode: "text/x-php",
    indentUnit: 4,
    tabSize: 4,
    indentWithTabs: false,
    enterMode: "keep",
    tabMode: "shift",
    gutter: true,
    onBlur: function(){
        phpeditor.save();
    }
});
</script>