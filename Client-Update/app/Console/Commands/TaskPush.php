<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TaskPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:push';

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
        $commitTags = [
            '[FEATURE]',
            '[FIX]',
            '[REFACTOR]',
            '[DOCUMENTATION]',
            '[TEST]',
            '[STYLE]',
            '[PERFORMANCE]',
            '[REVERT]',
            '[DEPENDENCY]',
            '[CONFIG]',
            '[RELEASE]'
        ];

        $commitTag = $this->choice('What type of commit is this? ', $commitTags, 0);
    
        $commitMessage = $this->ask('What is the commit message?');
        

        $commitMessage = "Task: " . $commitTag . "\n" . $commitMessage;

        $this->info($commitMessage);

        $this->info('Pushing to git...');
        $current_branch = exec('git rev-parse --abbrev-ref HEAD');
        exec('git add .');
        exec('git commit -m "' . $commitMessage . '"');
        $this->info('Pushed to git...');
        exec('git push origin ' . $current_branch);
        $this->info('Pushed to git.');
    }
}
