<?php

namespace Drupal\factory_bridge\Service;

/**
 * Service for calculating report data.
 */
class ReportCalculator {

  /**
   * The SQLite reader service.
   *
   * @var \Drupal\factory_bridge\Service\SQLiteReader
   */
  protected $sqliteReader;

  /**
   * Constructs a new ReportCalculator.
   */
  public function __construct(SQLiteReader $sqliteReader) {
    $this->sqliteReader = $sqliteReader;
  }

  /**
   * Calculates report data for an order.
   *
   * @param string $orderInternalId
   *   The order internal ID.
   *
   * @return array
   *   Array of tasks with calculations.
   */
  public function calculateForOrder($orderInternalId) {
    $tasks = $this->sqliteReader->getAssignedTasks($orderInternalId);
    $report = [];

    foreach ($tasks as $task) {
      $taskCode = $task['task_code'];
      $plan = (float) $task['applied_norm_hours'];

      $sessions = $this->sqliteReader->getWorkSessions($taskCode);
      $fact = 0;
      foreach ($sessions as $session) {
        $start = strtotime($session['start_time']);
        $end = strtotime($session['end_time']);
        if ($start && $end) {
          $fact += ($end - $start) / 3600; // Convert to hours
        }
      }

      $delta = $plan - $fact;

      $report[] = [
        'task_code' => $taskCode,
        'plan' => $plan,
        'fact' => $fact,
        'delta' => $delta,
      ];
    }

    return $report;
  }

}
