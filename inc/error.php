<?php

    function customErrorHandler($errCode, $errMsg, $file, $line)
    {
        $baseName = bin2hex(trim((string)strrchr($file, "/"), "/"));
        $errCodes = [E_ERROR => 'E_ERROR', E_WARNING => 'E_WARNING', E_PARSE => 'E_PARSE', E_NOTICE => 'E_NOTICE', E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING', E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING', E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING', E_USER_NOTICE => 'E_USER_NOTICE', E_STRICT => 'E_STRICT', E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR', E_DEPRECATED => 'E_DEPRECATED', E_USER_DEPRECATED => 'E_USER_DEPRECATED'];
        $ignoredErrCodes = [];
        if ((int)preg_match_all('/~(\S+)/', (string)ini_get('error_reporting'), $ematches) > 0)
        {
            foreach ($ematches[1] as $em)
            {
                if (defined($em)) $ignoredErrCodes[constant($em)] = $em;
            }
        }
        if (!isset($ignoredErrCodes[$errCode]))
        {
            $errorCategory = $error = "";
            $date = new DateTimeImmutable();
            $timestamp = "[" . $date->format('D M j H:i:s.u Y') . "] ";
            $errMsg = preg_replace('/AuthWHMCS/i', "DeleteOldArray", preg_replace('/\n|\r/', "", preg_replace('/\s*Stack trace.+/is', "", $errMsg)));
            $errMsg = ($baseName == "436f72652e706870") ? preg_replace('/\S*license\S*/i', "\$arrayKeyChange", $errMsg) : $errMsg;
            if (isset($errCodes[$errCode]))
            {
                $errorCategory = (preg_match('/_ERROR/', $errCodes[$errCode]) == 1) ? "Fatal error" : $errorCategory;       
                $errorCategory = (empty($errorCategory) && preg_match('/_WARNING/', $errCodes[$errCode]) == 1) ? "Warning" : $errorCategory;
                $errorCategory = (empty($errorCategory) && preg_match('/_PARSE/', $errCodes[$errCode]) == 1) ? "Parse error" : $errorCategory;
                $errorCategory = (empty($errorCategory)) ? "Notice" : $errorCategory;
                $error .= $errorCategory . ": " . $errCodes[$errCode] . "[" . $errCode . "]: \"" . $errMsg . "\" on line " . $line . " in file " . $file . "\n";
            }
            else
            {
                $errorCategory = "Unknown error";
                $error .= $errorCategory . ": [" . $errCode . "]: \"" . $errMsg . "\" on line " . $line . " in file " . $file . "\n";
            }
            $fp = fopen(dirname(__DIR__) . '/store/.ht_error_log', 'a');
            //fwrite($fp, print_r($ignoredErrCodes, true) . "\n");
            fwrite($fp, $timestamp . $error);
            fclose($fp);
            if (in_array($errorCategory, ["Fatal error", "Parse error"])) die('<!DOCTYPE html>
            <html lang="en"><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body><div style="margin:100px auto 0 auto;width:90%;text-align:center"><p><svg fill="#000000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="150px" height="150px" viewBox="0 0 82.796 82.796" xml:space="preserve"><g><path d="M41.399,0C18.85,0,0.506,14.084,0.506,31.396c0,13.068,10.471,24.688,26.232,29.314c-0.316,4.892-1.662,9.507-4.01,13.747 c-1.92,3.466-2.352,5.477-1.488,6.938c0.523,0.892,1.475,1.401,2.609,1.401c0.004,0,0.008,0,0.012,0 c1.508,0,5.52-0.051,30.909-21.728c16.481-4.36,27.521-16.237,27.521-29.673C82.292,14.084,63.945,0,41.399,0z M53.295,57.221 l-0.463,0.117l-0.363,0.311c-17.201,14.707-24.262,19.146-27.018,20.48c0.201-0.445,0.479-1.002,0.859-1.689 c2.926-5.283,4.471-11.082,4.588-17.231l0.031-1.618l-1.568-0.402C14.55,53.369,4.599,43.003,4.599,31.396 c0-15.053,16.508-27.301,36.799-27.301c20.29,0,36.797,12.248,36.797,27.301C78.195,43.053,68.189,53.432,53.295,57.221z M44.469,12.298c0.246,0.252,0.379,0.592,0.369,0.943l-0.859,26.972c-0.018,0.707-0.598,1.271-1.305,1.271h-2.551 c-0.709,0-1.287-0.563-1.305-1.271l-0.859-26.972c-0.01-0.352,0.123-0.691,0.369-0.943c0.246-0.251,0.582-0.394,0.934-0.394h4.273 C43.887,11.905,44.223,12.047,44.469,12.298z M44.783,47.312v4.885c0,0.72-0.584,1.304-1.305,1.304h-4.16 c-0.721,0-1.305-0.584-1.305-1.304v-4.885c0-0.72,0.584-1.304,1.305-1.304h4.16C44.199,46.009,44.783,46.593,44.783,47.312z"/></g></svg></p><p style="font:bold 18px Verdana,Arial">Oops, that\'s a <span style="color:red">' . ucwords($errorCategory) . '</span> !</p><p style="font:bold 14px Verdana,Arial">Please check the error log for more details.</p></div></body></html>');
        }
        return true;
    }
    
    function checkForFatalError()
    {
        $error = error_get_last();
        //die(print_r($error));
        if (isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE]))
        {
            customErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    set_error_handler("customErrorHandler");
    register_shutdown_function("checkForFatalError");
?>