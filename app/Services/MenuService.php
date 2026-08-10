<?php

namespace App\Services;

use App\Models\MenuModule;
use App\Models\UserTypeMenuPermission;
use App\Models\UserMenuPermission;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MenuService
{
    public function page(string $view, string $active, array $data = [])
    {
        if (! session('logged_in') && ! auth()->check()) {
            return redirect('/login');
        }

        if (! $this->canAccess($active)) {
            return redirect('/dashboard');
        }

        return view($view, array_merge([
            'institutionName' => session('institution_name', 'NiceAdmin'),
            'menus' => $this->menus($active),
            'activePage' => $active,
        ], $data));
    }

    public function menus(string $active = 'dashboard'): array
    {
        return $this->applyNotificationBadges($this->filterMenus($this->catalog($active)));
    }

    public function permissionMenus(): array
    {
        $menus = $this->catalog('__permission_catalog__');

        return collect($menus)->map(function (array $group): array {
            $items = collect($group['items'])->flatMap(function (array $item): array {
                $rows = [['key' => $item['key'], 'label' => $item['label'], 'url' => $item['url'] ?? '#']];

                foreach ($item['children'] ?? [] as $child) {
                    $rows[] = ['key' => $child['key'], 'label' => $child['label'], 'url' => $child['url'] ?? '#'];

                    foreach ($child['children'] ?? [] as $nested) {
                        $rows[] = ['key' => $nested['key'], 'label' => $nested['label'], 'url' => $nested['url'] ?? '#'];
                    }
                }

                return $rows;
            })->unique('key')->values()->all();

            return [
                'title' => $group['title'] ?: 'Main',
                'items' => collect($items)->filter(fn (array $item): bool => ($item['url'] ?? '#') !== '#')->values()->all(),
            ];
        })->filter(fn (array $group): bool => !empty($group['items']))->values()->all();
    }

    public function canAccess(string $active): bool
    {
        if ($active === 'dashboard' || session('permission_bypass', false)) {
            return true;
        }

        return $this->allowedMenuKeys()->contains($active);
    }

    public function catalog(string $active = 'dashboard'): array
    {
        try {
            if (! Schema::hasTable('tbl_menus')) {
                return [];
            }

            $modules = MenuModule::query()
                ->where('is_menu', true)
                ->orderBy('parent_id')
                ->orderBy('parent_id2')
                ->orderBy('module_id')
                ->get();

            if ($modules->isEmpty()) {
                return [];
            }

            return $this->buildMenusFromModules($modules, $active);
        } catch (Throwable) {
            return [];
        }
    }

    private function buildMenusFromModules($modules, string $active): array
    {
        $childrenByParent = $modules->groupBy(fn (MenuModule $module) => $this->effectiveParentId($module));

        $items = collect($childrenByParent->get(0, []))
            ->map(fn (MenuModule $module): array => $this->moduleMenuItem($module, $childrenByParent, $active))
            ->values()
            ->all();

        return [
            ['title' => '', 'items' => $items],
        ];
    }

    private function effectiveParentId(MenuModule $module): int
    {
        return (int) ($module->parent_id2 ?: $module->parent_id ?: 0);
    }

    private function moduleMenuItem(MenuModule $module, $childrenByParent, string $active): array
    {
        $children = collect($childrenByParent->get($module->module_id, []))
            ->map(fn (MenuModule $child): array => $this->moduleMenuItem($child, $childrenByParent, $active))
            ->values()
            ->all();

        $key = $module->name;
        $url = $module->route_path ?: '#';
        $item = [
            'key' => $key,
            'label' => $module->label ?: $module->name,
            'icon' => $module->menu_icon ?: 'bi-circle',
            'url' => $url,
            'active' => $this->isActiveModule($key, $url, $active),
        ];

        if (!empty($children) || (int) $module->collapse === 1) {
            $item['children'] = $children;
            $item['open'] = $item['active']
                || collect($children)->contains(fn (array $child): bool => !empty($child['active']) || !empty($child['open']));
            $item['chevron'] = true;
        }

        if ($module->is_dashboard) {
            $item['badge'] = 'Home';
        }

        if ($module->new_message) {
            $item['badge'] = (string) $module->new_message;
        }

        return $item;
    }

    private function isActiveModule(string $key, string $url, string $active): bool
    {
        if ($active === $key || $active === $url) {
            return true;
        }

        if ($url === '#') {
            return false;
        }

        return request()->is(trim($url, '/'));
    }

    private function filterMenus(array $menus): array
    {
        if (session('permission_bypass', false)) {
            return $menus;
        }

        $allowed = $this->allowedMenuKeys();

        return collect($menus)->map(function (array $group) use ($allowed): array {
            $group['items'] = collect($group['items'])->map(function (array $item) use ($allowed): ?array {
                if (!empty($item['children'])) {
                    $item['children'] = collect($item['children'])->map(function (array $child) use ($allowed): ?array {
                        if (!empty($child['children'])) {
                            $child['children'] = collect($child['children'])
                                ->filter(fn (array $nested): bool => $allowed->contains($nested['key']))
                                ->values()
                                ->all();

                            return !empty($child['children']) ? $child : null;
                        }

                        return $allowed->contains($child['key']) ? $child : null;
                    })->filter()->values()->all();

                    return !empty($item['children']) ? $item : null;
                }

                return $allowed->contains($item['key']) ? $item : null;
            })->filter()->values()->all();

            return $group;
        })->filter(fn (array $group): bool => !empty($group['items']))->values()->all();
    }

    private function allowedMenuKeys()
    {
        $allowed = collect(session('allowed_menu_keys', []))->push('dashboard');
        $privilegeId = session('privilege_id');
        $userId = session('user_id');

        if ($privilegeId && Schema::hasTable('user_type_menu_permissions')) {
            $allowed = $allowed->merge(
                UserTypeMenuPermission::query()
                    ->where('privilege_id', $privilegeId)
                    ->where('can_access', true)
                    ->pluck('menu_key')
            );
        }

        if ($userId && Schema::hasTable('user_menu_permissions')) {
            $allowed = $allowed->merge(
                UserMenuPermission::query()
                    ->where('user_id', $userId)
                    ->where('can_access', true)
                    ->pluck('menu_key')
            );
        }

        return $allowed->unique()->values();
    }

    private function notificationCounts(): array
    {
        $subdepartmentId = session('active_subdepartment_id');
        $module = session('active_subdepartment_module');

        if (! $subdepartmentId || ! $module) {
            return [];
        }

        $counts = [];

        if ($module === 'storage-supplies') {
            $pending = \App\Models\StoreRequisition::where('subdepartment_id', $subdepartmentId)
                ->where('status', 'pending')->count();

            $counts['storage-supplies.ordering.pending'] = $pending;
            $counts['storage-supplies.ordering.group'] = $pending;
            $counts['storage-supplies.setup'] = $pending;

            $pendingGrnApproval = \App\Models\GrnPurchaseOrder::where('Sub_Department_ID', $subdepartmentId)
                ->where('status', 'pending_approval')->count();

            $counts['storage-supplies.grn.approve'] = $pendingGrnApproval;
            $counts['storage-supplies.grn.group'] = $pendingGrnApproval;
            $counts['storage-supplies.setup'] = ($counts['storage-supplies.setup'] ?? 0) + $pendingGrnApproval;

            $requisitionPendingApproval = \App\Models\Requisition::where('requesting_subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();
            $counts['storage-supplies.requisition.approve'] = $requisitionPendingApproval;
            $counts['storage-supplies.requisition.group'] = ($counts['storage-supplies.requisition.group'] ?? 0) + $requisitionPendingApproval;

            $issueNoteAwaiting = \App\Models\Requisition::where('issuing_subdepartment_id', $subdepartmentId)
                ->where('status', 'approved')->whereDoesntHave('issueNote')->count();
            $issueNotePendingApproval = \App\Models\IssueNote::whereHas('requisition', fn ($q) => $q->where('issuing_subdepartment_id', $subdepartmentId))
                ->where('status', 'pending_approval')->count();
            $counts['storage-supplies.issue-note.new'] = $issueNoteAwaiting;
            $counts['storage-supplies.issue-note.approve'] = $issueNotePendingApproval;
            $counts['storage-supplies.issue-note.group'] = $issueNoteAwaiting + $issueNotePendingApproval;

            $grnIssueAwaiting = \App\Models\IssueNote::whereHas('requisition', fn ($q) => $q->where('requesting_subdepartment_id', $subdepartmentId))
                ->where('status', 'approved')->whereDoesntHave('grnAgainstIssueNote')->count();
            $grnIssuePendingApproval = \App\Models\GrnAgainstIssueNote::whereHas('issueNote.requisition', fn ($q) => $q->where('requesting_subdepartment_id', $subdepartmentId))
                ->where('status', 'pending_approval')->count();
            $counts['storage-supplies.grn-issue.new'] = $grnIssueAwaiting;
            $counts['storage-supplies.grn-issue.approve'] = $grnIssuePendingApproval;
            $counts['storage-supplies.grn-issue.group'] = $grnIssueAwaiting + $grnIssuePendingApproval;

            $grnWithoutPoPending = \App\Models\GrnWithoutPo::where('subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();
            $counts['storage-supplies.grn-without-po.approve'] = $grnWithoutPoPending;
            $counts['storage-supplies.grn-without-po.group'] = ($counts['storage-supplies.grn-without-po.group'] ?? 0) + $grnWithoutPoPending;

            $grnOpenBalancePending = \App\Models\GrnOpenBalance::where('subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();
            $counts['storage-supplies.grn-open-balance.approve'] = $grnOpenBalancePending;
            $counts['storage-supplies.grn-open-balance.group'] = ($counts['storage-supplies.grn-open-balance.group'] ?? 0) + $grnOpenBalancePending;

            $returnInwardPending = \App\Models\Return_::where('from_subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();
            $returnListAwaiting = \App\Models\Return_::where('status', 'pending_receipt')
                ->where('to_subdepartment_id', $subdepartmentId)->count();
            $counts['storage-supplies.return.approve'] = $returnInwardPending;
            $counts['storage-supplies.return.return-list'] = $returnListAwaiting;
            $counts['storage-supplies.return.group'] = $returnInwardPending + $returnListAwaiting;

            $returnOutwardPending = \App\Models\ReturnOutward::where('subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();
            $counts['storage-supplies.return-outward.approve'] = $returnOutwardPending;
            $counts['storage-supplies.return-outward.group'] = ($counts['storage-supplies.return-outward.group'] ?? 0) + $returnOutwardPending;

            $transferPendingApproval = \App\Models\StoreTransfer::where('from_subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();
            $transferPendingReceipt = \App\Models\StoreTransfer::where('status', 'pending_receipt')
                ->where('to_subdepartment_id', $subdepartmentId)->count();
            $counts['storage-supplies.store-transfer.approve'] = $transferPendingApproval;
            $counts['storage-supplies.store-transfer.pending-receipt'] = $transferPendingReceipt;
            $counts['storage-supplies.store-transfer.group'] = $transferPendingApproval + $transferPendingReceipt;

            $adjustmentPending = \App\Models\StockAdjustment::where('subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();
            $counts['storage-supplies.stock-adjustment.approve'] = $adjustmentPending;
            $counts['storage-supplies.stock-adjustment.group'] = ($counts['storage-supplies.stock-adjustment.group'] ?? 0) + $adjustmentPending;

            // Roll every new pending count up into the top-level Storage and Supplies badge too.
            $counts['storage-supplies.setup'] = ($counts['storage-supplies.setup'] ?? 0)
                + $requisitionPendingApproval + $issueNoteAwaiting + $issueNotePendingApproval
                + $grnIssueAwaiting + $grnIssuePendingApproval + $grnWithoutPoPending + $grnOpenBalancePending
                + $returnInwardPending + $returnListAwaiting + $returnOutwardPending
                + $transferPendingApproval + $transferPendingReceipt + $adjustmentPending;
        }

        if ($module === 'procurement') {
            $storeRequisitions = \App\Models\StoreRequisition::where('status', 'approved')
                ->where('procurement_status', 'pending')
                ->whereDoesntHave('localPurchaseOrder')
                ->count();

            $drafts = \App\Models\LocalPurchaseOrder::where('procurement_subdepartment_id', $subdepartmentId)
                ->where('status', 'draft')->count();

            $pendingApproval = \App\Models\LocalPurchaseOrder::where('procurement_subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();

            $counts['Procurement.store-order-requisition'] = $storeRequisitions;
            $counts['procurement.purchase-requisition'] = $drafts;
            $counts['procurement.approve-lpo'] = $pendingApproval;
            $counts['Procurement.setup'] = $storeRequisitions + $drafts + $pendingApproval;
        }

        return $counts;
    }

    private function applyNotificationBadges(array $menus): array
    {
        $counts = $this->notificationCounts();

        if (empty($counts)) {
            return $menus;
        }

        $walk = function (array $item) use (&$walk, $counts): array {
            if (! empty($counts[$item['key']])) {
                $item['badge'] = (string) $counts[$item['key']];
            }

            if (! empty($item['children'])) {
                $item['children'] = array_map($walk, $item['children']);
            }

            return $item;
        };

        return collect($menus)->map(function (array $group) use ($walk): array {
            $group['items'] = array_map($walk, $group['items']);

            return $group;
        })->all();
    }
}
