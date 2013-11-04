<p>
    <b>Note:</b> See more about <a href="http://www.php.net/manual/en/reference.pcre.pattern.syntax.php" target="_blank">regular expressions</a>.
    Attention! the more word replacement, the less performance.
</p>

<span style="color: green; font-size: 15px;">{$result}</span>
<form action="{$PHP_SELF}" method="POST">
    <input type="hidden" name="csrf_code" value="{$CSRF}" />
    <input type="hidden" name="mod" value="tools">
    <input type="hidden" name="action" value="replaces">
    <input type="hidden" name="do" value="replace">
    <p>
        <label for="replaces"><b>Replacement (from=to)</b></label>
        <div><textarea id="replaces" name="replaces" style="width: 730px; height:500px;">{$replaces}</textarea></div>
    </p>
    <div><input type="submit" value="Save" /></div>
</form>