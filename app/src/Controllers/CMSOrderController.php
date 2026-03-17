<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CmsOrderService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSOrderController
{
    private CmsOrderService $service;

    public function __construct()
    {
        $this->service = new CmsOrderService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $search = trim((string)($_GET['search'] ?? ''));
        $statusFilter = trim((string)($_GET['status'] ?? ''));
        $sortColumn = trim((string)($_GET['sort'] ?? 'created_at'));
        $sortDirection = trim((string)($_GET['dir'] ?? 'DESC'));

        $orders = $this->service->searchOrders($search, $statusFilter, $sortColumn, $sortDirection);
        $statuses = $this->service->getStatuses();
        $exportScope = strtolower(trim((string)($_GET['scope'] ?? 'all')));
        if (!in_array($exportScope, ['all', 'user'], true)) {
            $exportScope = 'all';
        }
        $exportUserId = isset($_GET['user_id']) ? max(0, (int)$_GET['user_id']) : 0;

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../Views/cms/orders_index.php';
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $details = $this->getOrderDetailsOrRedirect($id);
        $order = $details['order'];
        $items = $details['items'];
        $statuses = $this->service->getStatuses();

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../Views/cms/order_edit.php';
    }

    public function exportOptions(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $details = $this->getOrderDetailsOrRedirect($id);
        $order = $details['order'];

        try {
            $availableColumns = $this->service->getAvailableExportColumnsForOrder($id);
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders/' . $id, true, 302);
            exit;
        }

        $defaultSelectedColumns = array_keys($availableColumns);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/cms/order_export.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $this->getOrderDetailsOrRedirect($id);

        try {
            Csrf::assertPost();

            $status = (string)($_POST['order_status'] ?? '');
            $quantities = $_POST['quantities'] ?? [];
            if (!is_array($quantities)) {
                $quantities = [];
            }

            $this->service->updateOrder($id, $status, $quantities);
            Flash::setSuccess('Order updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/orders/' . $id, true, 302);
        exit;
    }

    public function export(): void
    {
        AdminGuard::requireAdmin(true);

        $search = trim((string)($_GET['search'] ?? ''));
        $statusFilter = trim((string)($_GET['status'] ?? ''));
        $sortColumn = trim((string)($_GET['sort'] ?? 'created_at'));
        $sortDirection = trim((string)($_GET['dir'] ?? 'DESC'));
        $format = strtolower(trim((string)($_GET['format'] ?? 'csv')));
        $scope = strtolower(trim((string)($_GET['scope'] ?? 'all')));
        $userId = isset($_GET['user_id']) ? max(0, (int)$_GET['user_id']) : 0;
        $orderId = isset($_GET['order_id']) ? max(0, (int)$_GET['order_id']) : 0;
        $statusTotals = null;

        if (in_array($scope, ['all', 'user'], true)) {
            $statusTotals = [
                'payed' => 0.0,
                'pending' => 0.0,
                'general' => 0.0,
            ];

            $orderSummaries = $this->service->searchOrders($search, $statusFilter, $sortColumn, $sortDirection);
            foreach ($orderSummaries as $summary) {
                $summaryUserId = (int)($summary['user_id'] ?? 0);
                if ($scope === 'user' && $userId > 0 && $summaryUserId !== $userId) {
                    continue;
                }

                $amount = (float)($summary['total_amount'] ?? 0.0);
                $status = strtolower((string)($summary['status'] ?? 'pending'));

                if ($status === 'payed') {
                    $statusTotals['payed'] += $amount;
                } else {
                    $statusTotals['pending'] += $amount;
                }

                $statusTotals['general'] += $amount;
            }
        }

        $columnMap = $this->service->getExportColumns();
        $query = http_build_query([
            'search' => $search,
            'status' => $statusFilter,
            'sort' => $sortColumn,
            'dir' => $sortDirection,
            'scope' => $scope,
            'user_id' => $userId > 0 ? $userId : null,
            'order_id' => $orderId > 0 ? $orderId : null,
        ]);

        try {
            $exportRows = $this->service->buildExportRows(
                $scope,
                $orderId,
                $userId,
                $search,
                $statusFilter,
                $sortColumn,
                $sortDirection
            );
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders' . ($query !== '' ? ('?' . $query) : ''), true, 302);
            exit;
        }

        if ($scope === 'order' && $orderId > 0) {
            $availableForOrder = $this->service->getAvailableExportColumnsForOrder($orderId);
            $requestedColumns = $_GET['columns'] ?? [];
            $columns = $this->normalizeColumnsFromMap($requestedColumns, $availableForOrder, array_keys($availableForOrder));
            $columnMap = $availableForOrder;
        } else {
            $requestedColumns = $_GET['columns'] ?? [];
            $allColumnKeys = array_keys($columnMap);
            $columns = $this->normalizeColumnsFromMap($requestedColumns, $columnMap, $allColumnKeys);
        }

        $safeRows = [];
        foreach ($exportRows as $exportRow) {
            $row = [];
            foreach ($columns as $column) {
                if (!array_key_exists($column, $columnMap)) {
                    continue;
                }

                $row[$columnMap[$column]] = (string)($exportRow[$column] ?? '');
            }
            $safeRows[] = $row;
        }

        if (in_array($scope, ['all', 'user'], true) && is_array($statusTotals) && $safeRows !== []) {
            $summaryRows = $this->buildStatusSummaryRows($columns, $columnMap, $statusTotals);
            foreach ($summaryRows as $summaryRow) {
                $safeRows[] = $summaryRow;
            }
        }

        if ($format === 'excel') {
            $this->outputExcel($safeRows, $columns, $columnMap);
            return;
        }

        $this->outputCsv($safeRows, $columns, $columnMap);
    }

    /** @return array<string, mixed> */
    private function getOrderDetailsOrRedirect(int $id): array
    {
        $details = $this->service->findOrderDetails($id);
        if ($details !== null) {
            return $details;
        }

        Flash::setErrors(['general' => 'Order not found.']);
        header('Location: /cms/orders', true, 302);
        exit;
    }

    /** @param array<int, array<string, string>> $rows */
    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    private function outputCsv(array $rows, array $columns, array $columnMap): void
    {
        $filename = 'orders_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            http_response_code(500);
            echo 'Could not generate export.';
            exit;
        }

        $headerLabels = array_map(fn(string $column): string => (string)($columnMap[$column] ?? $column), $columns);
        fputcsv($output, $headerLabels, ',', '"', '\\');

        foreach ($rows as $row) {
            $line = [];
            foreach ($headerLabels as $label) {
                $line[] = $row[$label] ?? '';
            }
            fputcsv($output, $line, ',', '"', '\\');
        }

        fclose($output);
        exit;
    }

    /** @param array<int, array<string, string>> $rows */
    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    private function outputExcel(array $rows, array $columns, array $columnMap): void
    {
        $filename = 'orders_export_' . date('Ymd_His') . '.xml';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $headerLabels = array_map(fn(string $column): string => (string)($columnMap[$column] ?? $column), $columns);
        $numericLabels = [
            'Order ID',
            'User ID',
            'Order Item ID',
            'Event ID',
            'Quantity',
            'Price Per Item',
            'Line Total',
            'Order Total',
        ];

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"";
        echo " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\">";
        echo "<Worksheet ss:Name=\"Orders\"><Table>";

        echo '<Row>';
        foreach ($headerLabels as $label) {
            echo '<Cell><Data ss:Type="String">' . $this->xmlEscape($label) . '</Data></Cell>';
        }
        echo '</Row>';

        foreach ($rows as $row) {
            echo '<Row>';
            foreach ($headerLabels as $label) {
                $value = (string)($row[$label] ?? '');
                $isNumeric = in_array($label, $numericLabels, true) && is_numeric($value);
                $type = $isNumeric ? 'Number' : 'String';
                echo '<Cell><Data ss:Type="' . $type . '">' . $this->xmlEscape($value) . '</Data></Cell>';
            }
            echo '</Row>';
        }

        echo '</Table></Worksheet></Workbook>';

        exit;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /** @param array<int, string> $columns */
    /** @param array<string, string> $columnMap */
    /** @param array{payed: float, pending: float, general: float} $statusTotals */
    /** @return array<int, array<string, string>> */
    private function buildStatusSummaryRows(array $columns, array $columnMap, array $statusTotals): array
    {
        $labels = [];
        foreach ($columns as $column) {
            if (isset($columnMap[$column])) {
                $labels[$column] = $columnMap[$column];
            }
        }

        $labelColumnPreference = ['customer_name', 'event_title', 'order_id'];
        $labelColumn = null;
        foreach ($labelColumnPreference as $candidate) {
            if (isset($labels[$candidate])) {
                $labelColumn = $candidate;
                break;
            }
        }
        if ($labelColumn === null && $columns !== []) {
            $first = (string)$columns[0];
            if (isset($labels[$first])) {
                $labelColumn = $first;
            }
        }

        $totalColumnPreference = ['order_total', 'line_total', 'unit_price'];
        $totalColumn = null;
        foreach ($totalColumnPreference as $candidate) {
            if (isset($labels[$candidate])) {
                $totalColumn = $candidate;
                break;
            }
        }

        $buildSingleRow = function (string $label, float $value) use ($labels, $labelColumn, $totalColumn): array {
            $row = [];
            foreach ($labels as $displayLabel) {
                $row[$displayLabel] = '';
            }

            if ($labelColumn !== null) {
                $row[$labels[$labelColumn]] = $label;
            }

            if ($totalColumn !== null) {
                $row[$labels[$totalColumn]] = number_format($value, 2, '.', '');
            }

            return $row;
        };

        return [
            $buildSingleRow('TOTAL PAYED', (float)$statusTotals['payed']),
            $buildSingleRow('TOTAL PENDING', (float)$statusTotals['pending']),
            $buildSingleRow('TOTAL GENERAL', (float)$statusTotals['general']),
        ];
    }

    /** @param mixed $requestedColumns */
    /** @param array<string, string> $columnMap */
    /** @param array<int, string> $defaultColumns */
    /** @return array<int, string> */
    private function normalizeColumnsFromMap(mixed $requestedColumns, array $columnMap, array $defaultColumns): array
    {
        if (!is_array($requestedColumns) || $requestedColumns === []) {
            return $defaultColumns;
        }

        $allowed = array_keys($columnMap);
        $selected = [];
        foreach ($requestedColumns as $column) {
            $key = trim((string)$column);
            if ($key !== '' && in_array($key, $allowed, true) && !in_array($key, $selected, true)) {
                $selected[] = $key;
            }
        }

        return $selected !== [] ? $selected : $defaultColumns;
    }
}
