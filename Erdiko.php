<?php
/**
 * Erdiko
 *
 * All global helpers
 * 
 * @category	Erdiko
 * @package		Erdiko
 * @copyright 	Copyright (c) 2014, Arroyo Labs, www.arroyolabs.com
 * @author		John Arroyo, john@arroyolabs.com
 */

/**
 * Erdiko Class
 */
class Erdiko
{
	/**
 	 * Log Object
	 */
	protected static $_logObject=null; // @todo get rid of this...
	
	/**
	 * Load a template file from a module
	 *
	 * @param string $filename
	 * @param mixed $data , data to expose to template
	 * @todo can we deprecate this function and only use the one in the theme engine? -John
	 */
	public static function getTemplate($filename, $data)
	{
		if (is_file($filename))
		{
			ob_start();
			include $filename;
			return ob_get_clean();
		}
		return false;
	}

	/**
	 * Load a view from the current theme with the given data
	 * 
	 * @param string $viewName
	 * @param array $data
	 */
	public static function getView($viewName, $data = null, $templateRootFolder = null)
	{
		$view = new \erdiko\core\View($viewName, $data);
		//echo $templateRootFolder;
		if ($templateRootFolder != null)
		{
			$view->setTemplateRootFolder($templateRootFolder);
		}
		return  $view->toHtml();
	}
	
	/**
	 * Read JSON config file and return array
	 *
	 * @param string $file
	 * @return array $config
	 */
	public static function getConfigFile($file)
	{
		$data = str_replace("\\", "\\\\", file_get_contents($file));
		$json = json_decode($data, TRUE);
		
		return $json;
	}
	
	/**
	 * Get configuration
	 */
	public static function getConfig($name = 'default')
	{
		$filename = APPROOT.'/config/'.$name.'.json';
		return self::getConfigFile($filename);
		// return \erdiko\core\Config::getConfig($name)->getContext();
		// return self::getConfigFile($file);
	}
	
	/**
	 * Get the compiled application routes from the config files
	 * 
	 * @todo cache the loaded/compiled routes
	 */
	public static function getRoutes()
	{
		$file = APPROOT.'/config/application/routes.json';
		$applicationConfig = Erdiko::getConfigFile($file);
		
		return $applicationConfig['routes'];
	}
	
	/**
	 * Send email
	 * @todo add ways to swap out ways of sending
	 */
	public static function sendEmail($toEmail, $subject, $body, $fromEmail)
	{	
		$headers = "From: $fromEmail\r\n" .
			"Reply-To: $fromEmail\r\n" .
			"X-Mailer: PHP/" . phpversion();
		
		return mail($toEmail, $subject, $body, $headers);
	}
	
	/**
	 * log
	 *
	 * @usage Erdiko::log('Sample notice',Logger::LogLevel,'Default')
	 * Need to import erdiko\core\Logger to use this function
	 * @todo add log level as a number instead of a constant
	 * @return bool $sucess
	 */
	public static function log($logString, $logLevel = null, $logKey = null)
	{
		if(Erdiko::$_logObject==null)
		{
			$config = Erdiko::getConfig("application/default");
			$logFiles=$config["logs"]["files"][0];				
			$logDir=$config["logs"]["path"];
			Erdiko::$_logObject=new erdiko\core\Logger($logFiles,$logDir);
		}
		return Erdiko::$_logObject->log($logString, $logLevel, $logKey);
	}
	
	/**
	* Get the configured cache instance using name
	* 
	* @return cache $cache returns the instance of the cache type
	*/	
	public static function getCache($cacheType=null)
	{
		$config = Erdiko::getConfig("application/default");
		if(!isset($cacheType))
			$cacheType = "default";

		if(isset($config["cache"][$cacheType]))
		{
			$cacheConfig = $config["cache"][$cacheType];
			$class = "erdiko\core\cache\\".$cacheConfig["type"];
			return new $class;
		}
		else
			return false;
	}
}
