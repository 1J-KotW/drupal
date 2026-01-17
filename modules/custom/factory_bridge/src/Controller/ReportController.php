<?php

namespace Drupal\factory_bridge\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\factory_bridge\Service\ReportCalculator;

/**
 * Controller for displaying factory reports.
 */
class ReportController extends ControllerBase {

  /**
   * The report calculator service.
   *
   * @var \Drupal\factory_bridge\Service\ReportCalculator
   */
  protected $reportCalculator;

  /**
   * Constructs a new ReportController.
   */
  public function __construct(ReportCalculator $reportCalculator) {
    $this->reportCalculator = $reportCalculator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('factory_bridge.report_calculator')
    );
  }

  /**
   * Displays the report for an order.
   *
   * @param string $internal_id
   *   The order internal ID.
   *
   * @return array
   *   Render array.
   */
  public function report($internal_id) {
    \Drupal::logger('factory_bridge')->info('ReportController: Generating report page for @id', ['@id' => $internal_id]);
    $reportData = $this->reportCalculator->calculateForOrder($internal_id);
    \Drupal::logger('factory_bridge')->debug('Report data for @id: @data', ['@id' => $internal_id, '@data' => json_encode($reportData)]);

    return [
      '#theme' => 'factory_bridge_report',
      '#report_data' => $reportData,
      '#internal_id' => $internal_id,
    ];
  }

}
