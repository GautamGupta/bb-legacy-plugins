
<?php bb_get_header(); ?>
<div id="fb_connect_wraper">
<h2>Welcome to <?php bb_option('name'); ?></h2>
<div class="fb_connect_table">
<div class="fb_connect_table_inner">
<table width="100%" border="0" cellpadding="6">
  <tr>
    <td colspan="3">You have succesfully connected, please proceed with last step below.</td>
    </tr>
  <tr>
    <td width="25%" rowspan="4" align="center"><img src="https://graph.facebook.com/<?php fb_get_userdata('id'); ?>/picture"></td>
    <td width="25%" align="right">Name : </td>
    <td width="50%"><?php fb_get_userdata('name'); ?> </td>
  </tr>
  <tr>
    <td align="right">Gender :</td>
    <td><?php fb_get_userdata('gender'); ?></td>
  </tr>
  <tr>
    <td align="right">Email : </td>
    <td><?php fb_get_userdata('email'); ?></td>
  </tr>
    <tr>
    <td align="right">Profile Link :</td>
    <td><?php fb_get_userdata('link'); ?></td>
  </tr>
</table>

</div>
</div>


<h2>Facebook Connect</h2>
<div class="fb_connect_table">
<div class="fb_connect_table_inner">
<table width="100%" border="0" cellpadding="6">
  <tr>
    <td width="50%">Create New Account</td>
    <td width="50%">Existing Users - Login &amp; Link Account</span></td>
  </tr>
  <tr>
    <td align="right" valign="top"><form method="post" action="">
	<table width="100%" border="0" cellpadding="4" cellspacing="4">

          <tr>
            <td width="49%"><p>New Username</p></td>
            <td width="51%"><input type="text" name="fb_username" id="fb_username" size="10" title="'.$user->lang['USERNAME'].'" value="<?php fb_get_clean_username();?>" style="width:100%" /></td>
          </tr>
          <tr>
            <td colspan="2">You will login with facebook connect button from now on. However a random password will be sent to your email, which you may want to use in future, but that's fully optional!</td>
            </tr>
          <tr>
            <td colspan="2" align="center"><input type="submit" name="register" value="Register" /> 
              &nbsp;<input type="submit" name="cancel" value="Cancel" />
			  <input type="hidden" name="linktype" value="new" />			  </td>
          </tr>
      </table>
	  </form>
	  </td>
    <td valign="top">
	
	<div style="border-left:thin #999999 solid"><form method="post" action=""><table width="100%" border="0" cellpadding="4" cellspacing="4">
          <tr>
            <td width="50%">Username </td>
            <td width="50%"><input type="text" name="username" id="username" size="10" style="width:100%" /></td>
          </tr>
          <tr>
            <td>Password </td>
            <td><input type="password" name="password" id="password" size="10" style="width:100%" /></td>
          </tr>
          <tr>
            <td colspan="2" align="center"><input type="submit" name="link_account" value="Login" />&nbsp;
			<input type="submit" name="cancel" value="Cancel" /><input type="hidden" name="linktype" value="link" /></td>
          </tr>
      </table></form></div></td>
  </tr>
</table>
</div></div>
</div>
<?php bb_get_footer(); ?>
