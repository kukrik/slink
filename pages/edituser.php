<?php 
$_USER = Auth::getUser();
if(isset($_GET['page']) && $_GET['page'] == "edituser" 
	&& isset($_GET['id']) && intval($_GET['id']) > 0
	&& $_USER != null && ($_USER->allowed_admin || $_USER->id == intval($_GET['id'])))
{
	
	$id = (int)$_GET['id'];
	$user = DB::getUser($id);
	
	if(isset($_POST['editing']))
	{
		if(!isset($_POST['username']) || $_POST['username'] == null || strlen($_POST['username']) <= 0)
			Messenger::addBad("Username has to be set");
		else if(!User::isUsernameValid($_POST['username']))
			Messenger::addBad("The username is not valid");
		else if($user->username != $_POST['username'] && !User::isUsernameAvailable($_POST['username']))
			Messenger::addBad("The username is already in use");
		else if(isset($_POST['email']) && $_POST['email'] != null && strlen($_POST['email']) > 0 && !User::isEmailValid($_POST['email']))
			Messenger::addBad("Not a valid e-mail address");
		else if(isset($_POST['email']) && $_POST['email'] != null && strlen($_POST['email']) > 0 && $_POST['email'] != $user->email && !User::isEmailAvailable($_POST['email']))
			Messenger::addBad("E-mail address is already in use");
		else if(!array_key_exists($_POST['timezone'], TimeManager::getTimezones()))
			Messenger::addBad("The time zone is not valid");
		else 
		{
			if(Auth::isAllowedAdmin())
				$user->username = $_POST['username'];
				
			if(isset($_POST['password']) && strlen($_POST['password']) > 0)
			{
				$user->password = md5($_POST['password']);
				Auth::updatePassword($_POST['password']);
			}
			
			$user->email = $_POST['email'];
			
			$user->timezone = $_POST['timezone'];
			
			if(Auth::isAllowedAdmin()) {
				if(isset($_POST['allowed_admin']) && $_POST['allowed_admin'] == "true")
					$user->allowed_admin = 1;
				else 
					$user->allowed_admin = 0;
			}
			DB::editUser($user);
			Messenger::addGood("User details updated");
		}
	}
	Messenger::show();
?>
<div id="submenu">
<a href="index.php?page=users&del=<?php print $user->id; ?>">Delete</a>
</div>

<h2>Edit user</h2>
<div id="edituser" class="form_layout">
<form action="" method="post" enctype="multipart/form-data">
	<div class="row">
		<label for="username">Username:</label> 
		<div class="data">
		<input type="text" name="username" value="<?php print $user->username; ?>" id="username" <?php if(!Auth::isAllowedAdmin()) print "readonly=\"readonly\" "; ?>/>
		</div>
	</div>		
	<div class="row">
		<label for="password">New Password:</label>
		<div class="data"><input type="password" name="password" value="" id="password" /></div>
	</div>
	<div class="row">
		<label for="email">E-mail:</label>
		<div class="data"><input type="text" name="email" id="email" value="<?php print $user->email; ?>" /> </div>
	</div>
	<div class="row">
		<label for="email">Timezone:</label>
		<div class="data">
			<select name="timezone">
				<?php 
					if($user->timezone == null || strlen($user->timezone) <= 0)
						print "<option value=\"\">-</option>";
					foreach(TimeManager::getTimezones() as $timezone_key => $timezone_value){
						print "<option value=\"".$timezone_key."\"";
						if($user->timezone == $timezone_key)
							print " selected=\"selected\"";
						print ">".$timezone_value."</option>";	
					}
				?>
			</select>
		</div>
	</div>
<?php 
if(Auth::isAllowedAdmin()) {
?>
	<div class="row">
		<label for="allowed_admin">Is admin:</label>
		<div class="data"><input type="checkbox" name="allowed_admin" value="true" id="allowed_admin" <?php print $user->allowed_admin==1?"checked=\"checked\"":""; ?> /></div>
	</div>
<?php 
}
?>

<?php
/*
Bookmarklet in a more readable version:
javascript:(
	function(){
		alert('hey');
		var u = 'http://www.siineiolekala.net/slink/demo/index.php?u=' + encodeURIComponent(document.location.href);
		a=function(){
			if(!window.open(u))
				document.location.href=u;
		};
		if(/Firefox/.test(navigator.userAgent))
			setTimeout(a,0);
		else a();
		void(0);
	}
)()
*/
if($user->id == $_USER->id){
?>
	<div class="row">
		<label for="bookmarklet">Bookmarklet:</label> 
		<div class="data">
		<a onclick="alert('Drag this link to your bookmarks.'); return false;" href="javascript:(%20function(){var%20u%20=%20'<?php print Config::get("url_prefix"); ?>index.php?u='%20+%20encodeURIComponent(document.location.href);%20a=function(){%20if(!window.open(u))%20document.location.href=u;%20};%20if(/Firefox/.test(navigator.userAgent))%20setTimeout(a,0);%20else%20a();%20void(0);%20}%20)()">Slink</a>
		</div>
	</div>
<?php 
}
?>
	<div class="row">
		<div class="data">
		<input type="hidden" name="editing" value="true" />
		<input type="submit" value="Save" class="button" />
		</div>
	</div>
	
</form>
</div>
<?php 
}
?>
