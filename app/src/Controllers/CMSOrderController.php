<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CmsOrderExportHelperService;
use App\Services\CmsOrderExportOutputService;
use App\Services\CmsOrderExportService;
use App\Services\CmsOrderService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSOrderController
{
    private CmsOrderService $service;
    private CmsOrderExportService $exportService;
    private CmsOrderExportHelperService $exportHelper;
    private CmsOrderExportOutputService $exportOutput;

    public function __construct()
    {
        $this->service = new CmsOrderService();
        $this->exportService = new CmsOrderExportService($this->service);
        $this->exportHelper = new CmsOrderExportHelperService();
        $this->exportOutput = new CmsOrderExportOutputService();
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
            $availableColumns = $this->exportService->getAvailableExportColumnsForOrder($id);
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

        $columnMap = $this->exportService->getExportColumns();
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
            $exportRows = $this->exportService->buildExportRows(
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
            $availableForOrder = $this->exportService->getAvailableExportColumnsForOrder($orderId);
            $requestedColumns = $_GET['columns'] ?? [];
            $columns = $this->exportHelper->normalizeColumnsFromMap($requestedColumns, $availableForOrder, array_keys($availableForOrder));
            $columnMap = $availableForOrder;
        } else {
            $requestedColumns = $_GET['columns'] ?? [];
            $allColumnKeys = array_keys($columnMap);
            $columns = $this->exportHelper->normalizeColumnsFromMap($requestedColumns, $columnMap, $allColumnKeys);
        }

        $safeRows = $this->exportHelper->buildSafeRows($exportRows, $columns, $columnMap);

        if (in_array($scope, ['all', 'user'], true) && is_array($statusTotals) && $safeRows !== []) {
            $summaryRows = $this->exportHelper->buildStatusSummaryRows($columns, $columnMap, $statusTotals);
            foreach ($summaryRows as $summaryRow) {
                $safeRows[] = $summaryRow;
            }
        }

        if ($format === 'excel') {
            $this->exportOutput->outputExcel($safeRows, $columns, $columnMap);
            return;
        }

        $this->exportOutput->outputCsv($safeRows, $columns, $columnMap);
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
}
