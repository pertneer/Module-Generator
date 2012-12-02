<?php
/**
* @package module_generator
* @version $Id: $
* @copyright (c) 2012 Pertneer
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
*/
define('IN_ACP_GEN', true);

include('./constants.php');
include('./functions.php');
include('./template.php');
$debug = false;
$Year = strftime("%Y", time());

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

if(isset($_POST['submit'])){
	$classname			= strtolower(str_replace(' ', '_', $_POST['title']['title_pre']['title'])); // Same as filename, without extension. Example: acp_foobar.
	$packagename		= strtolower($_POST['title']['title_pre']['package']); // Either acp, mcp or ucp.
	$copyright_holder	= $_POST['author']['afield']['username']; // Your name
	$email				= $_POST['author']['afield']['email']; // author email
	$version			= $_POST['version']; // The module's version
}else{
	$classname			= '';
	$packagename		= '';
	$copyright_holder	= '';
	$email				= '';
	$version			= '';
}


$modes				= array(); // array of modes
$title				= strtoupper($classname); // The title (language string)
$template_name		= $classname; // Name of template file "acp_name" for "acp_name.html"
$language			= 'en'; //Language selection
$submit = (isset($_POST['submit'])) ? true : false;
$message = '';

$template = new template();
$template->set_custom_template('.', 'default');

if($submit)
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
	* @version $Id: $
	* @copyright (c) ' .$Year . ' ' . $copyright_holder .'
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
	* @version $Id: $
	* @copyright (c) ' .$Year . " " . $copyright_holder ."
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
	$path = array('root','adm', 'style');
	$message .= create_path($path);
	$message .= output_file($html_content, $packagename . '_' . $template_name . '.html', implode('/', $path) . '/' );

	// Language file

	// language/en/mods/info_$classname.php
	$lang_content = '<?php
		/**
	*
	* @package ' . $packagename .'
	* @version $Id: $
	* @copyright (c) ' .$Year . ' ' . $copyright_holder .'
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

	//permimssion file
	
	//language/en/acp/permissions_my_mod.php
	$perm_content = '<?php
/** 
* @package language(permissions)
* @copyright (c) ' .$Year . ' ' . $copyright_holder . '
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

	'S_ERROR_TITLE' => (isset($error['title'])) ? true : false,
	'S_ERROR_DESC' => (isset($error['desc'])) ? true : false,
	'S_ERROR_VERSION' => (isset($error['version'])) ? true : false,
	'S_ERROR_TARGET' => (isset($error['target'])) ? true : false,
	'S_ERROR_INSTALL_LEVEL' => (isset($error['install_level'])) ? true : false,
	'S_ERROR_INSTALL_TIME' => (isset($error['install_time'])) ? true : false,
	'S_ERROR_AUTHOR' => (isset($error['author'])) ? true : false,
	'S_ERRORS' => (($submit) && !empty($error)) ? true : false,

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