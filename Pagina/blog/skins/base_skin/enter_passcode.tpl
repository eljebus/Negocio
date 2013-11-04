<p style="text-align: center;">This name ({$name}) is owned by a registered user and<br>you must enter password to use it!</p>
<div  style="text-align: center;">
<form name="passwordForm" id="passwordForm" method="post" action="">
<input type="hidden" name="name" value="{$name}" />
<input type="hidden" name="comments" value="{$comments}" />
<input type="hidden" name="mail" value="{$mail}" />
<input type="hidden" name="ip" value="{$ip}" />
<input type="hidden" name="subaction" value="addcomment" />
<input type="hidden" name="show" value="{$show}" />
<input type="hidden" name="ucat" value="{$ucat}" />
Password: <input type="password" name="password" />
{$user_post_query}
<input type="submit" /> <br>
<input type="checkbox" {$remcheck} name="CNrememberPass" value="1" /> Remember password in cookie (hash format)
</form>
</div>