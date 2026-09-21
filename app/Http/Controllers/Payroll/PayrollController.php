<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollPartnerRequest;
use App\Http\Requests\Payroll\PayrollStaffRequest;
use App\Http\Resources\Payroll\PayrollPartnerResource;
use App\Http\Resources\Payroll\PayrollStaffResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Payroll\PayrollService;
use Carbon\Carbon;

final class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollService $payrollService,
    ) {}

    public function partners(PayrollPartnerRequest $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->filters();

        $startDate = Carbon::parse($filters['start_date']);
        $endDate = Carbon::parse($filters['end_date']);

        $query = Partner::withActiveYclients()
            ->select('id', 'name', 'yclients_id')
            ->when(!empty($filters['partner_id']), function ($q) use ($filters) {
                $q->whereIn('id', (array) $filters['partner_id']);
            });;

        $result = Pagination::paginate(
            query: $query,
            request: $request,
            searchColumns: ['name'],
            sortable: ['name']
        );

        $enrichedPartners = $this->payrollService->calculateTurnoverForPartners(
            partners: $result['list'],
            startDate: $startDate,
            endDate: $endDate
        );

        $result['list'] = PayrollPartnerResource::collection($enrichedPartners);

        return JsonResponse::Send($result);
    }

    public function staff(PayrollStaffRequest $request): \Illuminate\Http\JsonResponse
    {
        $filters = $request->filters();

        $startDate = Carbon::parse($filters['start_date']);
        $endDate = Carbon::parse($filters['end_date']);

        $partner = Partner::withActiveYclients()->findOrFail($filters['partner_id']);

        $turnover = $this->payrollService->calculateTurnoverForPartner(
            partner: $partner,
            startDate: $startDate,
            endDate: $endDate
        );

        $staffPayroll = $this->payrollService->calculatePayrollForStaff(
            yclientsId: $partner->yclients_id,
            startDate: $startDate,
            endDate: $endDate
        );

        return JsonResponse::Send([
            'data' => [
                'staff'    => PayrollStaffResource::collection($staffPayroll),
                'turnover' => $turnover,
            ],
        ]);
    }
}
