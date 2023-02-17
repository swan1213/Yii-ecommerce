<?php

namespace common\commands;

use common\models\Crontask;
use mpirogov\bus\interfaces\SelfHandlingCommand;
use Yii;
use yii\base\BaseObject;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class RunTaskCommand extends BaseObject implements SelfHandlingCommand
{
    /**
     * @var string
     */
    public $taskId;
    /**
     * @var string
     */
    public $action;
    /**
     * @var mixed
     */
    public $params;

    /**
     * @param AddToTimelineCommand $command
     * @return bool
     */
    public function handle($command)
    {

        if ( is_array($command->params) ){
            Yii::$app->runAction($command->action, $command->params);
        } else {
            Yii::$app->runAction($command->action);
        }

        $cronId = $command->taskId;

        $task = Crontask::findOne(['id' => $cronId]);

        if ( !empty($task) ){
            $task->completed = Crontask::COMPLETED_YES;
            return $task->save(true, ['completed']);

        }
        return true;
    }
}
