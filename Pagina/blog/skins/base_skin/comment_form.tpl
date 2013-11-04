<form onsubmit="return CNSubmitComment();" method="post" name="comment" id="comment" action="" style="clear: left;">
{$template_form}
<div>
    <input type="hidden" name="subaction" value="addcomment" />
    <input type="hidden" name="ucat" value="{$ucat}" />
    <input type="hidden" name="show" value="{$show}" />
    {$user_post_query}
    {$captcha_form}
    </div>
</form>
{$remember_js}