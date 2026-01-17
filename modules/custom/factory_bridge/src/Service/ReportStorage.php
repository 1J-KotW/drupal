<?php

namespace Drupal\factory_bridge\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Service for storing report data in Drupal.
 */
class ReportStorage {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The SQLite reader service.
   *
   * @var \Drupal\factory_bridge\Service\SQLiteReader
   */
  protected $sqliteReader;

  /**
   * The report calculator service.
   *
   * @var \Drupal\factory_bridge\Service\ReportCalculator
   */
  protected $reportCalculator;

  /**
   * Constructs a new ReportStorage.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, SQLiteReader $sqliteReader, ReportCalculator $reportCalculator) {
    $this->entityTypeManager = $entityTypeManager;
    $this->sqliteReader = $sqliteReader;
    $this->reportCalculator = $reportCalculator;
  }

  /**
   * Updates all reports.
   */
  public function updateReports() {
    \Drupal::logger('factory_bridge')->info('ReportStorage: Starting cron update of reports');
    $orders = $this->sqliteReader->getOrderItems();
    \Drupal::logger('factory_bridge')->info('ReportStorage: Processing @count orders', ['@count' => count($orders)]);
    foreach ($orders as $order) {
      \Drupal::logger('factory_bridge')->debug('Processing order @id', ['@id' => $order['internal_id']]);
      $reportData = $this->reportCalculator->calculateForOrder($order['internal_id']);
      $this->saveReport($order, $reportData);
    }
    \Drupal::logger('factory_bridge')->info('ReportStorage: Cron update completed');
  }

  /**
   * Saves a report for an order.
   *
   * @param array $order
   *   The order data.
   * @param array $reportData
   *   The calculated report data.
   */
  protected function saveReport($order, $reportData) {
    $node = $this->getOrCreateNode($order['internal_id']);
    $node->setTitle($order['title']);
    $node->set('body', json_encode($reportData));
    $node->save();
  }

  /**
   * Gets or creates a node for the order.
   *
   * @param string $internalId
   *   The order internal ID.
   *
   * @return \Drupal\node\NodeInterface
   *   The node.
   */
  protected function getOrCreateNode($internalId) {
    $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'factory_report',
      'field_internal_id' => $internalId, // Assume we have a field for this.
    ]);
    if ($nodes) {
      return reset($nodes);
    }
    $node = Node::create([
      'type' => 'factory_report',
      'field_internal_id' => $internalId,
    ]);
    return $node;
  }

}
