<?php
/**
* @package module_generator
* @version Ver_1.0.0
* @copyright (c) 2012 Pertneer
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
*/
define('IN_ACP_GEN', true);

include('./constants.php');
include('./functions.php');
include('./template.php');
$debug = true;
$Year = strftime("%Y", time());
$debugInfo = '';
$error = array();
if($debug == true){
	$debugInfo = serialize($_POST);
}

// The get the post vars
if(isset($_POST['submit'])){
//$submit = $_POST['pertneer'];
//echo 'Title: '. $_POST['title']['title_pre']['title'] .'<br />';
//echo 'Package: '. $_POST['title']['title_pre']['package'] .'<br />';
//echo 'Username: '. $_POST['author']['afield']['username'] .'<br />';
//echo 'Email: '. $_POST['author']['afield']['email'] .'<br />';
//echo 'Version: '. $_POST['version'] . '<br />';
//echo 'Target: '. $_POST['target'] . '<br />';
}else{
	$submit = '';
}
$classname			= '';
$packagename		= '';
$copyright_holder	= '';
$version			= '';

if(isset($_POST['submit'])){

	//check for errors
	if((isset($_POST['title']['title_pre']['title']) && $_POST['title']['title_pre']['title'] != '') && (isset($_POST['title']['title_pre']['package']) && $_POST['title']['title_pre']['package'] != '') && (isset($_POST['author']['afield']['username']) && $_POST['author']['afield']['username'] != '') && (isset($_POST['version']) && $_POST['version'] != ''))
	{
		$classname			= strtolower(str_replace(' ', '_', $_POST['title']['title_pre']['title'])); // Same as filename, without extension. Example: acp_foobar.
		$packagename		= strtolower($_POST['title']['title_pre']['package']); // Either acp, mcp or ucp.
		$copyright_holder	= $_POST['author']['afield']['username']; // Your name
		$email				= $_POST['author']['afield']['email']; // author email
		$version			= $_POST['version']; // The module's version
		$target				= $_POST['target'];
		$error['title'] 	= '';
		$error['package']	= '';
		$error['author']	= '';
		$error['version']	= '';
		$error['error']		= false;
	}
	else
	{
		if($_POST['title']['title_pre']['title'] == ''){
			$classname			= '';
			$error['title']='Need a name for project';
		}
		if($_POST['title']['title_pre']['package'] == ''){
			$packagename		= '';
			$error['package']='This should never happen';
		}
		if($_POST['author']['afield']['username'] == ''){
			$copyright_holder	= '';
			$error['author']='Author of the project is required';
		}
		if( $_POST['version'] == ''){
			$version			= '';
			$error['version']='Version number is required';
		}
		$email				= '';
		$target				= '';
	}
}

$modes				= array(); // array of modes
$title				= strtoupper($classname); // The title (language string)
$template_name		= $classname; // Name of template file "acp_name" for "acp_name.html"
$language			= 'en'; //Language selection
$submit = (isset($_POST['submit'])) ? true : false;
$message = '';

$template = new template();
$template->set_custom_template('.', 'default');

if($submit && $error['error'] == false)
{
	// Check that it's a valid version number
	if ($version == '')
	{
		$error['version'] = 'version';
	}
	else if (!preg_match('#(\d+)\.(\d+)\.\d+[a-z]?#', $version))
	{
		$error['version'] = 'version';
	}
	
	//module file

	//Properties
	// $u_action: Path to current module, including session id. This property is set automatically. Example: ./index.php?i=foobar&sid={SESSION_ID}&mode=bar
	// $tpl_path: Template file's filename, without extension. Should be set in the module. Example: acp_foobar
	// $page_title: Page title, should be set in the module.
	// $module_path: Path to module files. Set automatically. Example: ./includes/acp

	$acp_content = '<?php
	/**
	*
	* @package ' . $packagename .'
	* @version ' . $version . '
	* @targetversion ' . $target . '
	* @copyright (c) ' .$Year . ' ' . $copyright_holder . ' ' . $email . '
	* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
	*/

	/**
	* @ignore
	*/
	if(!defined(\'IN_PHPBB\'))
	{
		exit;
	}

	/**
	* @package '. $packagename .'
	*/
	class ' . $packagename .'_' . $classname .'
	{
		var $u_action;
		var $tpl_path;

		function main($id, $mode)
		{
			global $db, $user, $auth, $template;
			global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
			switch($mode)
			{
				case \'index\':
					$this->page_title	= \'' . strtoupper($packagename) . '_' . $title .'\';
					$this->tpl_name		= \'' . $packagename . '_' . $template_name .'\';
				break;
			}
			
			
			$error = array();
			$template->assign_vars(array(
				\'L_TITLE\'			=> $user->lang[\'' . strtoupper($packagename) . '_' . strtoupper($classname) . '_TITLE\'],
				\'L_TITLE_EXPLAIN\'	=> $user->lang[\''. strtoupper($packagename) . '_' . strtoupper($classname) . '_TITLE_EXPLAIN\'],

				\'S_ERROR\'			=> (sizeof($error)) ? true : false,
				\'ERROR_MSG\'			=> implode(\'<br />\', $error),

				\'U_ACTION\'			=> $this->u_action)
			);
		}
	}';

	$path = array('root','includes', $packagename);
	$message .= create_path($path);
	$message .= output_file($acp_content, $packagename . '_' . $classname . '.php', implode('/', $path) . '/' );

	//info file
	// The info file should be placed in the includes/{MODULECLASS}/info folder, and have the same filename as the module file. Example: includes/acp/info/acp_foobar.php.

	// includes/{MODULECLASS}/info/$classname.php
	$info_content = "<?php
	/**
	*
	* @package " . $packagename .'
	* @version ' . $version . '
	* @targetversion ' . $target . '
	* @copyright (c) ' .$Year . " " . $copyright_holder ." " . $email . "
	* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
	*/
	
	/**
	* @package $packagename
	*/
	class " . $packagename . '_' . $classname ."_info
	{
		function module()
		{
			return array(
				'filename'		=> '" . $packagename . "_" . $classname . "',
				'title'			=> '" . strtoupper($packagename) . "_" . $title . "',
				'version'		=> '$version',
				'modes'			=> array(
					'index'		=> array('title' => '" . strtoupper($packagename) . "_" . $title . "_TITLE', 'auth' => 'acl_a_', 'cat' => array('')),
					
				),
			);
		}

		function install()
		{
		}

		function uninstall()
		{
		}
	}";

	$path = array('root','includes', $packagename, 'info');
	$message .= create_path($path);
	$message .= output_file($info_content, $packagename . '_' . $classname . '.php', implode('/', $path) . '/' );

	// Template file

	// adm/style/$template_name.html
	$html_content =  "<!-- INCLUDE overall_header.html -->

	<div>Title: {L_TITLE}</div>
	<div>Title: {L_TITLE_EXPLAIN}</div>
	<div>Action: {U_ACTION}</div>

	<!-- INCLUDE overall_footer.html -->";
	//add check for acp
	if($packagename == 'acp')
	{
		$path = array('root','adm', 'style');
	}else{
		$path = array('root','styles','prosilver','template');
	}
	$message .= create_path($path);
	$message .= output_file($html_content, $packagename . '_' . $template_name . '.html', implode('/', $path) . '/' );

	// Language file

	// language/en/mods/info_$classname.php
	$lang_content = '<?php
		/**
	*
	* @package ' . $packagename .'
	* @version ' . $version . '
	* @targetversion ' . $target . '
	* @copyright (c) ' .$Year . ' ' . $copyright_holder .' ' . $email . '
	* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
	*/
	
	/**
	* DO NOT CHANGE
	*/
	if (empty($lang) || !is_array($lang))
	{
		$lang = array();
	}
	// DEVELOPERS PLEASE NOTE
	//
	// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
	//
	// Placeholders can now contain order information, e.g. instead of
	// \'Page %s of %s\' you can (and should) write \'Page %1$s of %2$s\', this allows
	// translators to re-order the output of data while ensuring it remains correct
	//
	// You do not need this where single placeholders are used, e.g. \'Message %d\' is fine
	// equally where a string contains only two placeholders which are used to wrap text
	// in a url you again do not need to specify an order e.g., \'Click %sHERE%s\' is fine

	$lang = array_merge($lang, array(
		\'' . strtoupper($packagename) . '_' . $title . '_TITLE\'						=> \'' . str_replace('_', ' ', $title) . ' Index\',
		\'' . strtoupper($packagename) . '_' . $title . '_TITLE_EXPLAIN\'				=> \'' . str_replace('_', ' ', $title) . ' explain\',
	));';

	$path = array('root', 'language', $language, 'mods');
	$message .= create_path($path);
	$message .= output_file($lang_content, 'info_' . $packagename . '_'. $classname . '.php', implode('/', $path) . '/' );

	if($packagename == 'acp')
	{
		//permimssion file
	
		//language/en/acp/permissions_my_mod.php
		$perm_content = '<?php
/** 
* @package language(permissions)
* @version ' . $version . '
* @targetversion ' . $target . '
* @copyright (c) ' .$Year . ' ' . $copyright_holder . ' ' . $email . '
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Create a new category named Download
$lang[\'permission_cat\'][\'' . $classname . '\'] = \'' . $classname . '\';
// User Permissions
$lang = array_merge($lang, array(

));
//Moderator Permissions
$lang = array_merge($lang, array(

));
// Administrator Permissions
$lang = array_merge($lang, array(
	\'acl_a_' . $classname . '_manage\'			=> array(\'lang\' => \'Can change ' . $classname . ' settings\', \'cat\' => \''.$classname.'\'),
));';

		$path = array('root', 'language', $language, $packagename);
		$message .= create_path($path);
		$message .= output_file($perm_content, 'permissions_' . $classname . '.php', implode('/', $path) . '/' );
	}

$install_content = '<?php
/**
* @author Unknown Bliss (Michael Cullum of http://unknownbliss.co.uk)
* @package umil
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define(\'UMIL_AUTO\', true);
define(\'IN_PHPBB\', true);
define(\'IN_INSTALL\', true);

$phpbb_root_path = (defined(\'PHPBB_ROOT_PATH\')) ? PHPBB_ROOT_PATH : \'../\';
$phpEx = substr(strrchr(__FILE__, \'.\'), 1);
include($phpbb_root_path . \'common.\' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup(\'mods/kb\');

$auth_settings = array();
if (!file_exists($phpbb_root_path . \'umil/umil_auto.\' . $phpEx))
{
	trigger_error(\'Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>\', E_USER_ERROR);
}

// Some blog files we need
//require \"{$phpbb_root_path}includes/mods/constants_blog.$phpEx\";

// The name of the mod to be displayed during installation.
$mod_name = \'' . $classname . '\';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = \'' . $classname . '_version\';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod\'s name you set to $mod_name above)
*/
$language_file = \'mods/' . $classname . '\';

/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the \'default\' and \'select_user\' options)
*/


/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = "{T_THEME_PATH}/images/image.png";

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$mod = array(
	\'name\'		=> \'' . $classname . '\',
	\'version\'	=> \'' . $version . '\',
	\'config\'	=> \'' . $classname . '_version\',
	\'enable\'	=> \'' . $classname . '_enable\',
);


$versions = array(

	\''. $version . '\'	=> array(


			\'module_add\' => array(
				// ACP
				array(\'acp\', \'ACP_CAT_DOT_MODS\', \'ACP_' . $title . '\'),
				array(\'acp\', \'ACP_' . $title . '\', array(
						\'module_basename\'		=> \'' . $classname . '\',
						\'modes\'					=> array(\'index\'),
					),
				),

				// Permissions
				array(\'acp\', \'ACP_' . $title . '\', array(
						\'module_basename\'		=> \'' . $classname . '_permissions\',
						\'modes\'					=> array(\'set_permissions\', \'set_roles\'),
					),
				),
				
				// Types
				array(\'acp\', \'ACP_' . $title .'\', array(
						\'module_basename\'		=> \'' . $classname . '_types\',
						\'modes\'					=> array(\'manage\'),
					),
				),

				// UCP
				array(\'ucp\', \'\', \'UCP_' . $title . '\'),
				array(\'ucp\', \'UCP_' . $title . '\', array(
						\'module_basename\'			=> \'' . $classname . '\',
						\'modes\'						=> array(\'front\'),
					),
				),

				// MCP
				array(\'mcp\', \'\', \'MCP_' . $title . '\'),
				array(\'mcp\', \'MCP_' . $title . '\', array(
						\'module_basename\'	=> \'' . $classname . '\',
						\'modes\'				=> array(\'index\'),
					),
				),
			),
		)

	);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . \'umil/umil_auto.\' . $phpEx);';
		$path = array('root', 'install');
		$message .= create_path($path);
		$message .= output_file($install_content, 'install_' . $classname . '.php', implode('/', $path) . '/' );

if($debug == true){
	output_file($debugInfo, 'debug.php', 'root/');
}

$zipName = str_replace('_', '', $classname);
$zipName = $zipName .'.zip';
Zip('root/', $zipName );
	header('Location: zip.php?zip=' . base64_encode($zipName));
}

$s_is_copy = false;
$s_is_delete = false;
$template->assign_vars(array(

	'INSTALL_LEVEL' => (isset($install_level)) ? $install_level : '',
	'INSTALL_TIME' => (isset($install_time)) ? $install_time : '',

	'LANG_SELECT' => lang_select(),
	'PACKAGE_SELECT' => package_select(),
	'LICENSE' => (!empty($license)) ? $license : 'http://opensource.org/licenses/gpl-license.php GNU General Public License v2',

	'MOD_VERSION' => (isset($version)) ? $version : '',

	'PHP_INSTALLER' => (!empty($php_installer)) ? $php_installer : '',
	'PHPBB_LATEST' => PHPBB_LATEST,

	'S_ERROR_TITLE' => (isset($error['title']) && $error['title'] != '') ? true : false,
	'S_ERROR_PACKAGE' => (isset($error['package']) && $error['package'] != '') ? true : false,
	'S_ERROR_VERSION' => (isset($error['version']) && $error['version'] != '') ? true : false,
	'S_ERROR_TARGET' => (isset($error['target']) && $error['target'] != '') ? true : false,
	'S_ERROR_INSTALL_LEVEL' => (isset($error['install_level'])) ? true : false,
	'S_ERROR_INSTALL_TIME' => (isset($error['install_time'])) ? true : false,
	'S_ERROR_AUTHOR' => (isset($error['author']) && $error['author'] != '') ? true : false,
	'S_ERRORS' => (($submit) && !$error['error']) ? true : false,

	'S_IS_COPY' => $s_is_copy,
	'S_IS_DELETE' => $s_is_delete,
	'S_IN_MODX_CREATOR' => true,
	'S_SUBMIT' => ($submit) ? true : false,
	
	'S_WARNING_TARGET' => (isset($warning['target'])) ? true : false,
	'S_WARNINGS' => (($submit) && !empty($warning)) ? true : false,

	'TARGET_VERSION' => (isset($target)) ? $target : '',
	'MESSAGE'		=> ($debug) ? $message : '',
));

$template->set_filenames(array(
	'body' => 'acp_generator.html'
));

$template->display('body');