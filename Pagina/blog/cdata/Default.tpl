<?php
///////////////////// TEMPLATE Default /////////////////////
$template_active = <<<HTML
<div style="width:100%; margin-bottom:30px;">
<h2><strong>{title}</strong> {star-rate}</h2>

<article style="text-align:justify; padding:3px; margin-top:3px; margin-bottom:5px; border-top:1px solid #D3D3D3;">{short-story}</article>


<div><em>Articulo{date} por {author}</em></div>
</div>
HTML;


$template_full = <<<HTML
<div style="width:420px; margin-bottom:15px;">
<div><strong>{title}</strong> {star-rate}</div>

{avatar}

<div style="text-align:justify; padding:3px; margin-top:3px; margin-bottom:5px; border-top:1px solid #D3D3D3;">{full-story}</div>

<div style="float: right;">{comments-num}Comentarios</div>

<div>[edit]Edit | [/edit]<em>Articulo{date} por {author}</em></div>
</div>
HTML;


$template_comment = <<<HTML
<div style="width: 400px; margin-bottom:20px;">

<div style="border-bottom:1px solid black;"> Por <strong>{author}</strong> @ {date}</div>

<div style="padding:2px; background-color:#F9F9F9">{comment}</div>

</div>
HTML;


$template_form = <<<HTML
  <table border="0" width="370" cellspacing="0" cellpadding="0">
    <tr>
      <td width="60">Nombre:</td>
      <td><input type="text" name="name" value="{username}"></td>
    </tr>
    <tr>
      <td>E-mail:</td>
      <td><input type="text" name="mail"  value="{usermail}"> (optional)</td>
    </tr>
    <tr>
      <td>Smile:</td>
      <td>{smilies}</td>
    </tr>
    <tr>
      <td colspan="2">
      <textarea cols="40" rows="6" id=commentsbox name="comments"></textarea><br />
      <input type="submit" name="submit" value="Add My Comment">
      {remember_me}
      </td>
    </tr>
  </table>
HTML;


$template_prev_next = <<<HTML
<p align="center">[prev-link]<< Anterior[/prev-link] {pages} [next-link]Siguiente >>[/next-link]</p>
HTML;


$template_comments_prev_next = <<<HTML
<p align="center">[prev-link]<< Anteriores[/prev-link] ({pages}) [next-link]Recientes >>[/next-link]</p>
HTML;


?>