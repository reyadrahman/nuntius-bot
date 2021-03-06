<?php

namespace Nuntius\Cron;

use Nuntius\CronTaskAbstract;
use Nuntius\CronTaskInterface;

/**
 * Example cron task. Just logging stuff.
 */
class LogThings extends CronTaskAbstract implements CronTaskInterface {

  /**
   * {@inheritdoc}
   */
  protected $period = '*/1 * * * *';

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->container->get('manager.entity')
      ->get('logger')
      ->save([
        'inside' => 'yes',
        'time' => date('d/m/Y H:i', time()),
      ]);
  }

}
