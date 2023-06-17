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
    protected $signature = 'task:start';

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
        
        config(['CEREBRO_EMAIL' => "abbas.r@icrewsystems.com"]);
        config(['CEREBRO_PASSWORD' => 'abbas.r@1234']);
        $email = config('CEREBRO_EMAIL');
        $password = config('CEREBRO_PASSWORD');

        $this->info($email);
        $this->info($password);
        if(!$email || !$password){
            $this->error('Please set CEREBRO_EMAIL and CEREBRO_PASSWORD in .env file');
            return;
        }


        // reqest to cerebro
        $client = new Client();
        $request = new Request('POST', 'https://cerebro.icrewsystems.com/api/v1/get-private-code?email=' . $email .'&password=' . base64_encode($password) . '&electron=0');
        $res = $client->sendAsync($request)->wait();
        $response = json_decode($res->getBody(), true);
        $privateCode = $response['privateCode'];
        
        $request = new Request('GET', 'https://cerebro.icrewsystems.com/api/v1/generate/' . $privateCode);
        $res = $client->sendAsync($request)->wait();
        $response = json_decode($res->getBody(), true);
        $res = $client->sendAsync($request)->wait();
        $response = json_decode($res->getBody(), true);
        $authtoken = $response['token'];
        
        $request = new Request('GET', 'https://cerebro.icrewsystems.com/api/v1/allusers');
        $res = $client->sendAsync($request)->wait();
        $users = json_decode($res->getBody(), true);
        
        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]['email_encrypted8'] == base64_encode($email)) {
                $userId = $users[$i]['id'];
                $username = $users[$i]['first_name'];
                break;
            }
        }
        
        $request = new Request('GET', 'https://cerebro.icrewsystems.com/api/v1/get-all-assigned-tasks?authenticated_user=' . $userId . '&token=' . $authtoken);
        $res = $client->sendAsync($request)->wait();
        $tasks = json_decode($res->getBody(), true);

        $choices = [];
        for ($i = 0; $i < count($tasks); $i++) {
            $choices[] = $tasks[$i]['id'] . ' - ' . $tasks[$i]['title'];
        }

        $task = $this->choice('Select a task', $choices, 0);
        $taskNumber = explode(' - ', $task)[0];
        $this->info('Task number: '.$taskNumber);
        

        // $request = new Request('GET', 'https://cerebro.icrewsystems.com/api/v1/create_new_branch_for_task?task_id=' . $taskNumber . '&authenticated_user=' . $userId . '&token=' . $authtoken);
        // $res = $client->sendAsync($request)->wait();
        // $response = json_decode($res->getBody(), true);

        $response = [
            'status' => 'success',
            'branch_name' => $username . '_task_' . $taskNumber
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
            $this->info('Task Branch created successfully');
        } else {
            $this->error('Task not found');
            $this->error($response['message']);
        }

    }
}
