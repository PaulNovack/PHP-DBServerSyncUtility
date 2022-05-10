<?php
// Run Processes in background and be able to get status of processes.
// Can use built in runAll() lets class control running
// OR
// can get status of processes and add processes manually and call runAllAsync and
// be responsible for checking the status of the Processes in the queue from in the program that is using this class
class ProcessControl
{
    // Start Process returns PID
    public $processes;
    public $processCount;
    public $maxProcesses;
    public $work;
    public $runsRemaining;
    public $runsCompleted;
    public $runsRunning;

    public function __construct(){
        $this->processes = [];
        $this->work = false;
        $this->runsRemaining = 0;
        $this->runsCompleted = 0;
        $this->runsRunning = 0;
        $this->maxProcesses = 5;
    }
    public function setMaxProcesses($maxProcesses){
        $this->maxProcesses = $maxProcesses;
    }
    public function addRun($program,$phpFile,$arguments = []){
        $run = new \stdClass;
        $run->arguments = [];
        $run->arguments[] = $arguments;
        $run->program = $program;
        $run->phpFile = $phpFile;
        $run->status = 0; // 0 - not started, 1 - running, 2 - completed
        $run->proc = null; // return for calling get proc info
        $run->pid = 0;
        $this->processes[] = $run;
        $this->work = true;
    }
    public function runAll(){
        $loopcount = 1;
        while($this->work) {
            // check for finished processes
            foreach ($this->processes as $run) {
                if ($run->status == 1) {
                    $proc_details = proc_get_status($run->proc);
                    if ($proc_details['running'] == false) {
                        $run->status = 2;
                        if ($proc_details['exitcode'] != 0) {
                            echo "There was an error with process running dumping run info and proc_details:" . PHP_EOL;
                            print_r($run);
                            print_r($proc_details);
                            die();
                        }
                        $this->processCount--;
                    }
                }
            }
            // start if needed
            if ($this->processCount < $this->maxProcesses) {
                foreach ($this->processes as $run) {
                    if ($run->status == 0) {
                        $run->pid = $this->exec($run);
                        $run->status = 1;
                        $this->processCount++;
                        if ($this->processCount >= $this->maxProcesses) {
                            break;
                        }
                    }
                }
            }
            // check for finished processes
            foreach ($this->processes as $run) {
                if ($run->status == 1) {
                    $proc_details = proc_get_status($run->proc);
                    if ($proc_details['running'] == false) {
                        $run->status = 2;
                        if ($proc_details['exitcode'] != 0) {
                            echo "There was an error with process running dumping run info and proc_details:" . PHP_EOL;
                            print_r($run);
                            print_r($proc_details);
                            die();
                        }
                        $this->processCount--;
                    }
                }
            }
            $this->work = false;
            $this->runsRemaining = 0;
            $this->runsCompleted = 0;
            $this->runsRunning = 0;
            foreach ($this->processes as $run) {
                if ($run->status == 0) {
                    $this->work = true;
                    $this->runsRemaining++;
                } else if ($run->status == 1) {
                    $this->work = true;
                    $this->runsRunning++;
                } else if ($run->status == 2) {
                    $this->runsCompleted++;
                }
            }

            echo "Loop Count: " . $loopcount++ . PHP_EOL;
            echo "Runs Remaining: " . $this->runsRemaining . PHP_EOL;
            echo "Runs Running: " . $this->runsRunning . PHP_EOL;
            echo "Runs Completed: " . $this->runsCompleted . PHP_EOL;
            echo "Sleeping for 1 seconds" . PHP_EOL;
            usleep(250000);
        }
    }
    public function runAllAsync(){
        $loopcount = 1;
        while($this->work) {
            // start if needed
            if ($this->processCount < $this->maxProcesses) {
                foreach ($this->processes as $run) {
                    if ($run->status == 0) {
                        $run->pid = $this->exec($run);
                        $run->status = 1;
                        $this->processCount++;
                        if ($this->processCount >= $this->maxProcesses) {
                            break;
                        }
                    }
                }
            }
            // check for finished processes
            foreach ($this->processes as $run) {
                if ($run->status == 1) {
                    $proc_details = proc_get_status($run->proc);
                    if ($proc_details['running'] == false) {
                        $run->status = 2;
                        if ($proc_details['exitcode'] != 0) {
                            echo "There was an error with process running dumping run info and proc_details:" . PHP_EOL;
                            print_r($run);
                            print_r($proc_details);
                            die();
                        }
                        $this->processCount--;
                    }
                }
            }
            $this->work = false;
            $this->runsRemaining = 0;
            $this->runsCompleted = 0;
            $this->runsRunning = 0;
            foreach ($this->processes as $run) {
                if ($run->status == 0) {
                    $this->work = true;
                    $this->runsRemaining++;
                } else if ($run->status == 1) {
                    $this->work = true;
                    $this->runsRunning++;
                } else if ($run->status == 2) {
                    $this->runsCompleted++;
                }
            }

            echo "Loop Count: " . $loopcount++ . PHP_EOL;
            echo "Runs Remaining: " . $this->runsRemaining . PHP_EOL;
            echo "Runs Running: " . $this->runsRunning . PHP_EOL;
            echo "Runs Completed: " . $this->runsCompleted . PHP_EOL;
            echo "Sleeping for 1 seconds" . PHP_EOL;
            usleep(250000);
            $this->work = false;
        }
        return true;
    }
    public function Exec(&$run){
        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $command = $run->program;
        if(isset($run->phpFile)){
            $command .= ' ' . $run->phpFile;
        }

        foreach($run->arguments[0] as $argument){
            $command .= ' ' . $argument;
        }
        $proc = proc_open($command, $descriptor, $pipes);
        $proc_details = proc_get_status($proc);
        // to do check proc details if not started die with message
        $run->proc = $proc;
        if($proc_details['running'] == false){
            die("Error starting process");
        }
        // only do max 4 requesta per second
        usleep(100000);
        return $proc_details['pid'];
    }
    // Kill a process
    public function kill($pid){
        return posix_kill($pid,0);
    }
}