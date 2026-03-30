<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Cms\Models\OrderExportRequest;
use App\Cms\Models\OrderSearchCriteria;
use App\Cms\Services\CmsOrderExportService;
use App\Cms\Services\CmsOrderExportResponder;
use App\Cms\Services\CmsOrderService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSOrderController
{
    private CmsOrderService $service;
    private CmsOrderExportService $exportService;
    private CmsOrderExportResponder $exportResponder;

    public function __construct()
    {
        $this->service = new CmsOrderService();
        $this->exportService = new CmsOrderExportService($this->service);
        $this->exportResponder = new CmsOrderExportResponder();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        try {
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
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $details = $this->getOrderDetailsOrRedirect($id);
            $order = $details['order'];
            $items = $details['items'];
            $statuses = $this->service->getStatuses();

            $errors = Flash::getErrors();
            $flashSuccess = Flash::getSuccess();
            $csrfToken = Csrf::token();

            require __DIR__ . '/../../Views/cms/order_edit.php';
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders', true, 302);
            exit;
        }
    }

    public function exportOptions(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $details = $this->getOrderDetailsOrRedirect($id);
            $order = $details['order'];
            $availableColumns = $this->exportService->getAvailableExportColumnsForOrder($id);

            $defaultSelectedColumns = array_keys($availableColumns);
            $errors = Flash::getErrors();
            $flashSuccess = Flash::getSuccess();

            require __DIR__ . '/../../Views/cms/order_export.php';
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders/' . $id, true, 302);
            exit;
        }
    }

    public function exportOptionsScope(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $request = OrderExportRequest::fromArray($_GET);
            if (!in_array($request->scope, ['all', 'user'], true)) {
                $request->scope = 'all';
            }

            $availableColumns = $this->exportService->getExportColumns();
            $defaultSelectedColumns = array_keys($availableColumns);

            $errors = Flash::getErrors();
            $flashSuccess = Flash::getSuccess();

            require __DIR__ . '/../../Views/cms/order_export_scope.php';
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders', true, 302);
            exit;
        }
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            $this->getOrderDetailsOrRedirect($id);
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

        try {
            $request = OrderExportRequest::fromArray($_GET);
            $result = $this->exportService->prepareExport($request);

            $this->exportResponder->stream($result);
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/orders', true, 302);
            exit;
        }
    }

    /** @return array<string, mixed> */
    private function getOrderDetailsOrRedirect(int $id): array
    {
        try {
            $details = $this->service->findOrderDetails($id);
            if ($details !== null) {
                return $details;
            }

            Flash::setErrors(['general' => 'Order not found.']);
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/orders', true, 302);
        exit;
    }


}
