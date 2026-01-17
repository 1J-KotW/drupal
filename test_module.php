<?php

// Simulate the module functionality

// Mock the database connection
class MockSQLiteReader {
  public function getOrderItems() {
    return [
      ['internal_id' => 'ORD001', 'title' => 'Product A', 'code' => 'PA001'],
      ['internal_id' => 'ORD002', 'title' => 'Product B', 'code' => 'PB001'],
    ];
  }

  public function getAssignedTasks($orderId) {
    if ($orderId == 'ORD001') {
      return [
        ['task_code' => 'TASK001', 'applied_norm_hours' => 10.0],
        ['task_code' => 'TASK002', 'applied_norm_hours' => 5.0],
      ];
    } elseif ($orderId == 'ORD002') {
      return [
        ['task_code' => 'TASK003', 'applied_norm_hours' => 8.0],
      ];
    }
    return [];
  }

  public function getWorkSessions($taskCode) {
    $sessions = [
      'TASK001' => [
        ['start_time' => '2023-01-01 08:00:00', 'end_time' => '2023-01-01 10:00:00'], // 2 hours
        ['start_time' => '2023-01-01 15:00:00', 'end_time' => null], // active, ignore
      ],
      'TASK002' => [
        ['start_time' => '2023-01-01 10:00:00', 'end_time' => '2023-01-01 13:00:00'], // 3 hours
      ],
      'TASK003' => [
        ['start_time' => '2023-01-01 14:00:00', 'end_time' => '2023-01-01 18:00:00'], // 4 hours
      ],
    ];
    return $sessions[$taskCode] ?? [];
  }
}

class ReportCalculator {
  protected $sqliteReader;

  public function __construct($sqliteReader) {
    $this->sqliteReader = $sqliteReader;
  }

  public function calculateForOrder($orderInternalId) {
    $tasks = $this->sqliteReader->getAssignedTasks($orderInternalId);
    $report = [];

    foreach ($tasks as $task) {
      $taskCode = $task['task_code'];
      $plan = (float) $task['applied_norm_hours'];

      $sessions = $this->sqliteReader->getWorkSessions($taskCode);
      $fact = 0;
      foreach ($sessions as $session) {
        if ($session['end_time']) {
          $start = strtotime($session['start_time']);
          $end = strtotime($session['end_time']);
          if ($start && $end) {
            $fact += ($end - $start) / 3600; // Convert to hours
          }
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

// Test
$reader = new MockSQLiteReader();
$calculator = new ReportCalculator($reader);

echo "Report for ORD001:\n";
$report = $calculator->calculateForOrder('ORD001');
foreach ($report as $item) {
  echo "Task: {$item['task_code']}, Plan: {$item['plan']}, Fact: {$item['fact']}, Delta: {$item['delta']}\n";
}

echo "\nReport for ORD002:\n";
$report = $calculator->calculateForOrder('ORD002');
foreach ($report as $item) {
  echo "Task: {$item['task_code']}, Plan: {$item['plan']}, Fact: {$item['fact']}, Delta: {$item['delta']}\n";
}
