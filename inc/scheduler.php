<?php 
    use MediaConverterPro\lib\ipgen\IpGenerator;
    use MediaConverterPro\lib\TrustedSession;
    use MediaConverterPro\lib\Config;
    use GO\Scheduler;
    
    include_once 'autoload.php';  // Autoload class files
    include_once dirname(__DIR__) . '/vendors/scheduler/inc/autoload.php';

    // Create new objects
    $scheduler = new Scheduler();
    $ipGen = new IpGenerator();
    $sess = new TrustedSession();

    // ... configure the scheduled jobs (see below) ...
    $scheduler->call(function() {return true;})->onlyOne()->at('* * * * *');  // "Placeholder" job that ensures subsequent job output is flushed from output buffer
    if (Config::_ENABLE_DYNAMIC_IPV6)
    {
        echo "\nAttempting IpGenerator task...\n";
        $scheduler->call($ipGen->run())->onlyOne()->at(Config::_DYNAMIC_IPV6_FREQUENCY);
    }
    echo "\nAttempting TrustedSession task...\n";
    $scheduler->call($sess->generate())->onlyOne()->at('0 */2 * * *');
    
    //echo "\n" . print_r($scheduler->getQueuedJobs(), true) . "\n";

    // Let the scheduler execute jobs which are due.
    $jobs = $scheduler->run();
    if (count($jobs) > 0)
    {
        foreach ($jobs as $job)
        {
            if ($job == current($jobs)) continue;  // Skip the "Placeholder" job
            echo "\nOutput for Job (" . $job->getId() . ")\n";
            echo "----------\n";
            echo $job->getOutput();
        }
    }
    echo "\nScheduled tasks have been executed\n";
?>