<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html 
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Code Igniter JSON Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Evan Baliatico
 * @link		http://www.codeigniter.com/wiki/
 */

// ------------------------------------------------------------------------

/* Loading the helper automatically requires and instantiates the Services_JSON class */
if ( ! class_exists('Services_JSON'))
{
	require_once(BASEPATH.'helpers/JSON.php');		
}
$json = new Services_JSON();

/**
 * json_encode
 *
 * Encodes php to JSON code.  Parameter is the data to be encoded.
 *
 * @access	public
 * @param	string
 * @return	string
 */	
function fjson_encode($data = null)
{
	if($data == null) return false;
	$json = new Services_JSON();
	return $json->encode($data);
}
	
// ------------------------------------------------------------------------

/**
 * json_decode
 *
 * Decodes JSON code to php.  Parameter is the data to be decoded.
 *
 * @access	public
 * @param	string
 * @return	string
 */	
function fjson_decode($data = null)
{
	if($data == null) return false;
$json = new Services_JSON();
	return $json->decode($data);
}
	
// ------------------------------------------------------------------------



?>
