<?php

    namespace MediaConverterPro\lib\ipgen;

    class IpGenerator extends BaseIpGenerator
    {
        public function run(): callable
        {
            return function() {
                $retval = "";
                $output = $this->getNetworkInfo(); 
                if (!empty($output['networkName']) && !empty($output['currentIp']))
                {
                    $output['prefix'] = $this->getIPv6Prefix($output['currentIp']);
                    if (!empty($output['prefix']))
                    {
                        $output['newIp'] = $this->generateRandomIPv6($output['prefix']);
                        if (!empty($output['newIp']))
                        {
                            $configNewIP = 'ip -6 addr add ' . $output['newIp'] . '/64 dev ' . $output['networkName'];
                            $retval .= "\nRunning Command: " . $configNewIP . "\n";
                            
                            $result = [];
                            exec($configNewIP, $result);
                            $retval .= print_r($result, true);
            
                            $delOldIP = 'ip -6 addr del ' . $output['currentIp'] . '/64 dev ' . $output['networkName'];
                            $retval .= "\nRunning Command: " . $delOldIP . "\n";
                            
                            $result = [];
                            exec($delOldIP, $result);
                            $retval .= print_r($result, true);
                        } 
                    }
                }
                $retval .= "\nOutput:\n";
                $retval .= print_r($output, true);  
                return $retval;
            };
        }
    }

?>