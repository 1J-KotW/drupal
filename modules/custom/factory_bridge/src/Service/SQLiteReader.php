<?php

namespace Drupal\factory_bridge\Service;

use Drupal\Core\Database\Connection;

/**
 * Service for reading data from external SQLite database.
 */
class SQLiteReader {

  /**
   * The SQLite database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $sqliteConnection;

  /**
   * Constructs a new SQLiteReader.
   */
  public function __construct() {
    $this->sqliteConnection = \Drupal\Core\Database\Database::getConnection('sqlite_external');
  }

  /**
   * Gets all order items.
   *
   * @return array
   *   Array of order items.
   */
  public function getOrderItems() {
    $query = $this->sqliteConnection->select('order_items', 'oi');
    $query->fields('oi', ['internal_id', 'title', 'code']);
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    // Debug: Log data from SQLite
    \Drupal::logger('factory_bridge')->info('SQLiteReader: Retrieved @count order items from SQLite', ['@count' => count($result)]);
    foreach ($result as $item) {
      \Drupal::logger('factory_bridge')->debug('Order item: @id - @title', ['@id' => $item['internal_id'], '@title' => $item['title']]);
    }
    return $result;
  }

  /**
   * Gets assigned tasks for an order.
   *
   * @param string $orderInternalId
   *   The order internal ID.
   *
   * @return array
   *   Array of assigned tasks.
   */
  public function getAssignedTasks($orderInternalId) {
    $query = $this->sqliteConnection->select('assigned_tasks', 'at');
    $query->fields('at', ['task_code', 'applied_norm_hours']);
    $query->condition('order_internal_id', $orderInternalId);
    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

  /**
   * Gets work sessions for a task.
   *
   * @param string $taskCode
   *   The task code.
   *
   * @return array
   *   Array of work sessions.
   */
  public function getWorkSessions($taskCode) {
    $query = $this->sqliteConnection->select('work_sessions', 'ws');
    $query->fields('ws', ['start_time', 'end_time']);
    $query->condition('task_code', $taskCode);
    $query->isNotNull('end_time'); // Only completed sessions
    return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
  }

}
