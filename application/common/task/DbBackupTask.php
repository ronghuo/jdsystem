<?php
namespace app\common\task;
use app\common\library\DbBackupHelper;
use think\console\Command;
use think\console\Input;
use think\console\Output;
class DbBackupTask extends Command {

    protected function configure() {
        $this->setName('DbBackupTask')->setDescription("数据库备份任务计划");
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('数据库备份开始...');

        $dbBackupHelper = new DbBackupHelper();
        $dbBackupHelper->backall();

        $output->writeln('数据库备份完成...');
    }

}