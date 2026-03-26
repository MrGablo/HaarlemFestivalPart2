<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Cms\Models\OrderExportRequest;
use App\Cms\Models\OrderSearchCriteria;
use App\Cms\Services\CmsOrderExportService;
use App\Cms\Services\CmsOrderService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSOrderController
{
    private CmsOrderService $service;
    private CmsOrderExportService $exportService;

    public function __construct()
    {
        $this->service = new CmsOrderService();
        $this->exportService = new CmsOrderExportService($this->service);
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $criteria = OrderSearchCriteria::fromArray($_GET);

        $search = $criteria->search;
        $statusFilter = $criteria->statusFilter;
        $sortColumn = $criteria->sortColumn;
        $sortDirection = $criteria->sortDirection;

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

        require __DIR__ . '/../../Views/cms/orders_index.php';
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

        require __DIR__ . '/../../Views/cms/order_edit.php';
    }

    public function exportOptions(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $details = $this->getOrderDetailsOrRedirect($id);
        $order = $details['order'];

        try {
            $availableColumns = $this->exportService->getAvailableExportColumnsForOrder($id);
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders/' . $id, true, 302);
            exit;
        }

        $defaultSelectedColumns = array_keys($availableColumns);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../../Views/cms/order_export.php';
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

        $request = OrderExportRequest::fromArray($_GET);
        $query = http_build_query($request->toRedirectQueryParams());

        try {
            $result = $this->exportService->prepareExport($request);
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders' . ($query !== '' ? ('?' . $query) : ''), true, 302);
            exit;
        }

        if ($result->format === 'excel') {
            $this->outputExcel($result->rows, $result->columns, $result->columnMap);
            return;
        }

        $this->outputCsv($result->rows, $result->columns, $result->columnMap);
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

}
