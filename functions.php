<?php
/**
* @package module_generator
* @version Ver_1.0.0
* @copyright (c) 2012 Pertneer
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
*/

/** Sample Usage
* 1st_dir/2nd_dir/3rd_dir/
* $path = array('1st_dir', '2nd_dir', '3rd_dir');
* echo create_path($path);
* echo output_file($my_content, 'testFile.php', implode('/', $path) . '/');
*/

//zip function
function Zip($source, $destination)
{
	if (!extension_loaded('zip') || !file_exists($source)) {
		return false;
	}

	$zip = new ZipArchive();
	if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
		return false;
	}

	//$source = str_replace('\\', '/', realpath($source));

	if (is_dir($source) === true)
	{
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($files as $file)
		{
			$file = str_replace('\\', '/', $file);

			// Ignore "." and ".." folders
			if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
				continue;

			//$file = realpath($file);

			if (is_dir($file) === true)
			{
				$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
			}
			else if (is_file($file) === true)
			{
				$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
			}
		}
	}
	else if (is_file($source) === true)
	{
		$zip->addFromString(basename($source), file_get_contents($source));
	}

	return $zip->close();
}

// path is an array of directories
function create_path($path)
{
	$message = '';
	if(is_array($path)){
		$previous_dir = '';
		foreach($path as $dir){
			if($previous_dir == ''){
				$message .= create_dir($dir);
				$previous_dir .= $dir;
			}else{
				$message .= create_dir($previous_dir . '/' .$dir);
				$previous_dir .= '/' .$dir;
			}
		}
	}else{
		$message .= create_dir($path);
	}
	return $message;
}

// creates a single directory for a given path
function create_dir($dir_name, $dir_path = '')
{
	$message = '';
	if(!is_dir($dir_path . $dir_name))
	{
		mkdir($dir_path . $dir_name, 0700);
		$message = '';
	}else{
		$message = 'Directory Exists';
	}
	return $message;
}

// output the information to a file at a given path
function output_file($content, $filename, $path)
{
	$file = $path . $filename;

	if(!file_exists($file)){
		$handle = fopen($file, "x");
		fclose($handle);
	}

	// Let's make sure the file exists and is writable first.
	if (is_writable($file)) {

		// In our example we're opening $filename in append mode.
		// The file pointer is at the bottom of the file hence
		// that's where $somecontent will go when we fwrite() it.
		if (!$handle = fopen($file, 'w')) {
			$message = "Cannot open file ($file)";
			return $message;
		}

		// Write $somecontent to our opened file.
		if (fwrite($handle, $content) === FALSE) {
			$message = "Cannot write to file ($file)";
			return $message;
		}

		$message = '';

		fclose($handle);

	} else {
		$message = "The file $file is not writable";
	}
	
	return $message;
}

/**
*
* Make options for selecting package
* @return string, the package select
*/
function package_select($package = 'ACP')
{
	$target_package = array(
		'ACP' => 'ACP',
		'MCP' => 'MCP',
		'UCP' => 'UCP',
	);
	
	// What language are we gonna use
	$package = trim($package);
	$package = (isset($package[$target_package])) ? $lang : 'en';
	$package_options = '';
	foreach($target_package as $key => $value)
	{
		$package_options .= '<option value="' . $key . '"' . (($key == $package) ? ' selected="selected"' : '') . '>' . $value . '</option>';
	}
	return($package_options);
}

/**
* Make options for the language-select with the selected language set.
*
* @param string $lang, the language that should be selected as default
* @return string, the language select
*/
function lang_select($lang = 'en')
{
	$target_lang = array(
		'ab' => 'Abkhazian',
		'aa' => 'Afar',
		'af' => 'Afrikaans',
		'sq' => 'Albanian',
		'am' => 'Amharic',
		'ar' => 'Arabic',
		'hy' => 'Armenian',
		'as' => 'Assamese',
		'ay' => 'Aymara',
		'az' => 'Azerbaijani',
		'ba' => 'Bashkir',
		'eu' => 'Basque',
		'bn' => 'Bengali',
		'dz' => 'Bhutani',
		'bh' => 'Bihari',
		'bi' => 'Bislama',
		'br' => 'Breton',
		'bg' => 'Bulgarian',
		'my' => 'Burmese',
		'be' => 'Byelorussian',
		'km' => 'Cambodian',
		'ca' => 'Catalan',
		'zh' => 'Chinese',
		'co' => 'Corsican',
		'hr' => 'Croatian',
		'cs' => 'Czech',
		'da' => 'Danish',
		'nl' => 'Dutch',
		'en' => 'English',
		'eo' => 'Esperanto',
		'et' => 'Estonian',
		'fo' => 'Faeroese',
		'fj' => 'Fiji',
		'fi' => 'Finnish',
		'fr' => 'French',
		'fy' => 'Frisian',
		'gl' => 'Galician',
		'ka' => 'Georgian',
		'de' => 'German',
		'el' => 'Greek',
		'kl' => 'Greenlandic',
		'gn' => 'Guarani',
		'gu' => 'Gujarati',
		'ha' => 'Hausa',
		'iw' => 'Hebrew',
		'hi' => 'Hindi',
		'hu' => 'Hungarian',
		'is' => 'Icelandic',
		'in' => 'Indonesian',
		'ia' => 'Interlingua',
		'ik' => 'Inupiak',
		'ga' => 'Irish',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'jw' => 'Javanese',
		'kn' => 'Kannada',
		'ks' => 'Kashmiri',
		'kk' => 'Kazakh',
		'rw' => 'Kinyarwanda',
		'ky' => 'Kirghiz',
		'rn' => 'Kirundi',
		'ko' => 'Korean',
		'ku' => 'Kurdish',
		'lo' => 'Laothian',
		'la' => 'Latin',
		'lv' => 'Lettish',
		'ln' => 'Lingala',
		'lt' => 'Lithuanian',
		'mk' => 'Macedonian',
		'mg' => 'Malagasy',
		'ms' => 'Malay',
		'ml' => 'Malayalam',
		'mt' => 'Maltese',
		'mi' => 'Maori',
		'mr' => 'Marathi',
		'mo' => 'Moldavian',
		'mn' => 'Mongolian',
		'na' => 'Nauru',
		'ne' => 'Nepali',
		'no' => 'Norwegian',
		'oc' => 'Occitan',
		'or' => 'Oriya',
		'om' => 'Oromo',
		'ps' => 'Pashto',
		'fa' => 'Persian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'pa' => 'Punjabi',
		'qu' => 'Quechua',
		'rm' => 'Rhaeto-Romance',
		'ro' => 'Romanian',
		'ru' => 'Russian',
		'sm' => 'Samoan',
		'sg' => 'Sangro',
		'sa' => 'Sanskrit',
		'gd' => 'Scots Gaelic',
		'sr' => 'Serbian',
		'sh' => 'Serbo-Croatian',
		'st' => 'Sesotho',
		'tn' => 'Setswana',
		'sn' => 'Shona',
		'sd' => 'Sindhi',
		'si' => 'Singhalese',
		'ss' => 'Siswati',
		'sk' => 'Slovak',
		'sl' => 'Slovenian',
		'so' => 'Somali',
		'es' => 'Spanish',
		'su' => 'Sudanese',
		'sw' => 'Swahili',
		'sv' => 'Swedish',
		'tl' => 'Tagalog',
		'tg' => 'Tajik',
		'ta' => 'Tamil',
		'tt' => 'Tatar',
		'te' => 'Tegulu',
		'th' => 'Thai',
		'bo' => 'Tibetan',
		'ti' => 'Tigrinya',
		'to' => 'Tonga',
		'ts' => 'Tsonga',
		'tr' => 'Turkish',
		'tk' => 'Turkmen',
		'tw' => 'Twi',
		'uk' => 'Ukrainian',
		'ur' => 'Urdu',
		'uz' => 'Uzbek',
		'vi' => 'Vietnamese',
		'vo' => 'Volapuk',
		'cy' => 'Welsh',
		'wo' => 'Wolof',
		'xh' => 'Xhosa',
		'ji' => 'Yiddish',
		'yo' => 'Yoruba',
		'zu' => 'Zulu',
	);

	// What language are we gonna use
	$lang = trim($lang);
	$lang = (isset($lang[$target_lang])) ? $lang : 'en';
	$language_options = '';
	foreach($target_lang as $key => $value)
	{
		$language_options .= '<option value="' . $key . '"' . (($key == $lang) ? ' selected="selected"' : '') . '>' . $value . '</option>';
	}
	return($language_options);
}