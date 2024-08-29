<?php

    namespace MediaConverterPro\lib;

    use \MediaConverterPro\lib\extractors\YouTube;

    class TrustedSession
    {
        const _MAX_TRIES = 5;
        const _SLEEP_TIME = 10;  // In seconds
        private $_tries = 0;   
        
        public function generate(): callable
        {
            return function() {
                return $this->gen();
            };
        }
    
        // Private "Helper" Functions
        private function gen()
        {
            $retval = "";
            $ytsScriptDir = glob("/home/youtube-trusted-session*", GLOB_ONLYDIR);
            $chromium = glob("/usr/bin/chromium*");
            if ($ytsScriptDir !== false && !empty($ytsScriptDir) && $chromium !== false && !empty($chromium))
            {
                $ytsScriptDir = current($ytsScriptDir);
                $bunResponse = [];
                exec('type bun', $bunResponse);
                if (!empty($bunResponse) && preg_match('/^((bun is )(.+))/i', $bunResponse[0]) == 1)
                {
                    $bunResponse = [];
                    exec("bun run " . $ytsScriptDir . "/index.js", $bunResponse);
                    $json = json_decode(implode("", $bunResponse), true);
                    if (isset($json['visitorData'], $json['poToken'], $json['basejs']))
                    {
                        $retval .= "\n" . print_r($json, true) . "\n";

                        $basejsPath = $this->GetStoreDir() . YouTube::_BASE_JS;
                        $basejs = fopen($basejsPath, 'w');
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $json['basejs']);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_USERAGENT, Config::_REQUEST_USER_AGENT);
                        if (Config::_REQUEST_IP_VERSION != -1)
                        {
                            curl_setopt($ch, CURLOPT_IPRESOLVE, constant("CURL_IPRESOLVE_V" . (string)Config::_REQUEST_IP_VERSION));
                        }
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_FILE, $basejs);
                        $response = curl_exec($ch);
                        if (curl_errno($ch) != 0 || (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE) != 200 || empty($response))
                        {
                            $retval .= "\nThere may have been a problem saving base.js to \"store\" folder!\n";
                        }
                        curl_close($ch);
                        fclose($basejs);
                        $this->ChangeFileOwner($basejsPath);

                        $sessJsonPath = $this->GetStoreDir() . YouTube::_TRUSTED_SESS_JSON;
                        $fp = fopen($sessJsonPath, 'w');
                        if ($fp !== false && flock($fp, LOCK_EX))
                        {
                            $basejsCode = (is_file($basejsPath)) ? (string)file_get_contents($basejsPath) : '';
                            if (preg_match('/\WsignatureTimestamp:(\d+)\D/i', $basejsCode, $sigTimestamp) == 1)
                            {
                                $json['sigTimestamp'] = $sigTimestamp[1];
                            }
                            fwrite($fp, json_encode($json));
                            flock($fp, LOCK_UN);
                        }
                        fclose($fp);
                        $this->ChangeFileOwner($sessJsonPath);

                        $retval .= "\nCached trusted session info!\n";
                    }
                    else
                    {
                        $retval .= "\nInvalid Bun script response or Missing some response info!\n";
                        $this->_tries++;
                        if ($this->_tries < self::_MAX_TRIES)
                        {
                            $retval .= "\nWaiting " . self::_SLEEP_TIME . " seconds to try again...\n";
                            sleep(self::_SLEEP_TIME);
                            $retval .= "\nTrying again.\n";
                            $retval .= $this->gen();
                        }
                    }
                }
                else
                {
                    $retval .= "\nBun is not installed or installed incorrectly!\n";
                }
            }
            else
            {
                $retval .= "\n\"youtube-trusted-session\" Bun script and/or chromium is not installed!\n";
            }
            return $retval;
        }

        private function ChangeFileOwner($path)
        {
            if (is_file($path)) 
            {
                chown($path, get_current_user());
            }
        }
        
        private function GetStoreDir()
		{
			return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'store' . DIRECTORY_SEPARATOR;
		}
    }

?>