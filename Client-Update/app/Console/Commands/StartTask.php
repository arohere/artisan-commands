<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class StartTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:start {taskNumber}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // user inputs task number as a parameter
        // cerebro post with username and password from env to fetch branch name
        // if success then create, publish and checkout to the new branch
        // cerebro post confirmation
        // else if failure display error message locally
        
        if ($this->argument('taskNumber')) {
            $taskNumber = $this->argument('taskNumber');
        } else {
            $taskNumber = $this->ask('Enter task number');
        }

        // reqest to cerebro
        // $client = new Client();
        // $request = new Request('POST', 'https://cerebro.icrewsystems.com/api/v1/task/?email=abbas.r@icrewsystems.com&password=XXXXXXXXX&electron=0&task='.$taskNumber);
        // $res = $client->sendAsync($request)->wait();
        // $response = json_decode($res->getBody(), true);

        $response = [
            'status' => 'success',
            'branch_name' => 'task-'.$taskNumber
        ];
        
        if($response['status'] == 'success'){
            $branchName = $response['branch_name'];
            $this->info('Branch name: '.$branchName);
            $this->info('Creating branch...');
            exec('git checkout -b '.$branchName);
            $this->info('Branch created');
            $this->info('Publishing branch...');
            exec('git push origin '.$branchName);
            $this->info('Branch published');
            $this->info('Checking out to branch...');
            exec('git checkout '.$branchName);
            $this->info('Checked out to branch');
            $this->info('Task started successfully');
        } else {
            $this->error('Task not found');
            $this->error($response['message']);
        }

    }
}
