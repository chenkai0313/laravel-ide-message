<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SchedulePush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'it\'s time to send schedule push';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$this->info($this->description);
        $this->error('Something went wrong!');*/

        $result = \MessageService::scheduleMessage(['statement' => '开始执行推送脚本']);
        $this->info($result['msg']);
        file_put_contents(
            storage_path().'/logs/schedule-'.date('Y-m-d').'-logs.log',
            '[data]'.var_export("执行时间: ".date('Y-m-d H:i:s')." 实施队列补发  返回说明：".$result['msg'], true).PHP_EOL,
            FILE_APPEND
        );
        return true;
    }
}
