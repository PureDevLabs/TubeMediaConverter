<?php 
    use MediaConverterPro\lib\ipgen\IpGenerator;
    use MediaConverterPro\lib\Config;
    use GO\Scheduler;
    
    include_once 'autoload.php';  // Autoload class files
    include_once dirname(__DIR__) . '/vendors/scheduler/inc/autoload.php';

    // Create new objects
    $scheduler = new Scheduler();
    $ipGen = new IpGenerator();

    // ... configure the scheduled jobs (see below) ...
    if (Config::_ENABLE_DYNAMIC_IPV6)
    {
        $scheduler->call($ipGen->run())->onlyOne()->at(Config::_DYNAMIC_IPV6_FREQUENCY);
        echo "\nIpGenerator task was attempted\n";
    }
    
    //echo "\n" . print_r($scheduler->getQueuedJobs(), true) . "\n";

    // Let the scheduler execute jobs which are due.
    $jobs = $scheduler->run();
    echo (count($jobs) > 0) ? $jobs[0]->getOutput() : '';
    echo "\nScheduled tasks have been executed\n";
?>