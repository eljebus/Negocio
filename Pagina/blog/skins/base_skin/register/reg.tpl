<form  name=login action="{$PHP_SELF}" method=post>
    <input type=hidden name=action value=doregister>
    <table leftmargin=0 marginheight=0 marginwidth=0 topmargin=0 border=0 height=100% cellspacing=0>
     <tr>
       <td width=80>Username:</td>
       <td colspan="2"><input tabindex=1 type=text name=regusername  style="width:134" size="20"></td>
     </tr>
     <tr>
       <td width=80>Nickname:</td>
       <td colspan="2"><input tabindex=1 type=text name=regnickname  style="width:134" size="20"></td>
     </tr>
     <tr>
       <td width=80>Password:</td>
       <td>
        <div><input tabindex=1 type="password" name=regpassword id="regpassword" onkeyup="password_strength();" style="width:134" size="20"></div>
        <div id="password_strength"></div></td>
       <td>&nbsp;<input type="text" style="border: none; width: 150px;" id="pass_msg" disabled="true" value="Enter password"></td>
     </tr>
     <tr>
       <td width=80>Confirm:</td>
       <td colspan="2"><input tabindex=1 type="password" name="confirm" style="width:134" size="20"></td>
     </tr>
     <tr>
       <td width=80>Email:</td>
       <td colspan="2"><input tabindex=1 type=text name=regemail  style="width:134" size="20"></td>
     </tr>
     <tr>
       <td width=80>Captcha:</td>
       <td colspan="2"><input tabindex=1 type=text name="captcha" style="width:134" size="20"></td>
     </tr>
     <tr>
       <td width=80><a href="#" style="border-bottom: 1px dotted #000080;" onclick="getId('capcha').src='captcha.php?r='+Math.random(); return(false);">Refresh code</a></td>
       <td colspan="2"><img src="captcha.php" id="capcha" alt=""></td>
     </tr>
      <tr>
       <td>&nbsp;</td>
       <td colspan="2"><input accesskey="s" type=submit style="background-color: #F3F3F3;" value='Register'></td>
      </tr>
      <tr>
       <td align=center colspan=3>{$result}</td>
      </tr>
    </table>
</form>