<?php

	namespace MediaConverterPro\app;

	use MediaConverterPro\app\Core\Controller;
	use MediaConverterPro\lib\Config;

	// App Controller class
	abstract class AppController extends Controller
	{
		// Protected Fields
		protected $_globalAppVars = array();		
		
		#region Callbacks
		protected function beforeAction()
		{
			parent::beforeAction();
			// Open MySQL (and, if enabled, Redis) connection
			$dboCredentials = [
				'server' => Config::_SERVER, 
				'dbUser' => Config::_DB_USER, 
				'dbPassword' => Config::_DB_PASSWORD, 
				'db' => Config::_DATABASE
			];
			if (Config::_ENABLE_REDIS_CACHING)
			{
				$dboCredentials = [
					'redisServer' => Config::_REDIS_SERVER,
					'redisPort' => Config::_REDIS_PORT,
					'redisPassword' => Config::_REDIS_PASSWORD
				] + $dboCredentials;
			}
			$this->SetDatabaseObject($dboCredentials);			
			$this->_globalAppVars = $this->GetVars();
			
			// AntiCaptcha Plugin variables
			$acInstalled = isset(Config::$_plugins['AntiCaptcha']) && file_exists(dirname(__DIR__) . "/vendors/AntiCaptcha");	
			$this->_globalAppVars['AntiCaptcha']['Remote'] = ($acInstalled) ? 'MediaConverterPro\\vendors\\AntiCaptcha\\lib\\Remote' : 'MediaConverterPro\\lib\\Remote';
			//die($this->_globalAppVars['AntiCaptcha']['Remote']);
			$this->_globalAppVars['AntiCaptcha']['Extractors'] = ($acInstalled) ? 'MediaConverterPro\\vendors\\AntiCaptcha\\lib\\extractors\\' : 'MediaConverterPro\\lib\\extractors\\';
			
			$this->SetHelpers(array('MetaData'));
			$session = $this->GetSession();
			if (!isset($session['appSecretToken']))
			{
				$this->SetSession(array('appSecretToken' => sha1(mt_rand())));
			}
		}
		#endregion
		
		#region "Shared" Controller Methods
		protected function FileGetContents($url, $postData='', $reqHeaders=array())
		{
			$file_contents = '';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if (!empty($postData))
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);					
			}
			if (!empty($reqHeaders))
			{
				curl_setopt($ch, CURLOPT_HTTPHEADER, $reqHeaders);
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$file_contents = curl_exec($ch);
			$isCurlError = curl_errno($ch) != 0;
			$curlInfo = curl_getinfo($ch);
			curl_close($ch);
			return (!$isCurlError && substr($curlInfo['http_code'], 0, 1) != '4') ? $file_contents : '';
		}
		#endregion
	}

?>