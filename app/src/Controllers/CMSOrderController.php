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
        $exportColumns = $this->service->getExportColumns();
        $selectedExportColumns = $this->service->normalizeSelectedColumns($_GET['columns'] ?? []);
        $exportScope = strtolower(trim((string)($_GET['scope'] ?? 'all')));
        if (!in_array($exportScope, ['all', 'user', 'order'], true)) {
            $exportScope = 'all';
        }
        $exportUserId = isset($_GET['user_id']) ? max(0, (int)$_GET['user_id']) : 0;
        $exportOrderId = isset($_GET['order_id']) ? max(0, (int)$_GET['order_id']) : 0;

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

        $columns = $this->service->normalizeSelectedColumns($_GET['columns'] ?? []);
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
        fputcsv($output, $headerLabels);

        foreach ($rows as $row) {
            $line = [];
            foreach ($headerLabels as $label) {
                $line[] = $row[$label] ?? '';
            }
            fputcsv($output, $line);
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
}
