<?php

declare(strict_types=1);

namespace App\Services\Formatters;

final class CompanyStaffFormatter
{
    /**
     * @param array{
     *     name: string,
     *     specialization: string,
     *     phone: ?string
     * } $staffData
     */
    public function formatCreated(array $staffData, string $branchName): string
    {
        $phone = $staffData['phone'] ?: __('reports.company_staff.not_specified');
        $specialization = $staffData['specialization'] ?: __('reports.company_staff.specialization_not_specified');
        $name = $staffData['name'];
        $header = __('reports.company_staff.created_header');

        return implode("\n", [
            "🟢 {$header}",
            '',
            __('reports.company_staff.branch', ['branch' => $branchName]),
            __('reports.company_staff.name', ['name' => $name]),
            __('reports.company_staff.specialization', ['specialization' => $specialization]),
            __('reports.company_staff.phone', ['phone' => $phone]),
        ]);
    }

    /**
     * @param array{
     *     name: string,
     *     specialization: string,
     *     phone: ?string,
     *     fired: bool
     * } $staffData
     */
    public function formatUpdated(array $staffData, string $branchName, array $changes): string
    {
        $isFired = $staffData['fired'] || (!empty($changes['fired']['new']));
        $icon = $isFired ? '🔴' : '🟡';
        $header = __('reports.company_staff.updated_header');

        $fields = [
            'name' => [
                'label'   => __('reports.company_staff.fields.name'),
                'current' => $staffData['name'],
                'format'  => fn ($val) => $val ?: __('reports.company_staff.not_specified'),
            ],
            'specialization' => [
                'label'   => __('reports.company_staff.fields.specialization'),
                'current' => $staffData['specialization'] ?: __('reports.company_staff.specialization_not_specified'),
                'format'  => fn ($val) => $val ?: __('reports.company_staff.specialization_not_specified'),
            ],
            'phone' => [
                'label'   => __('reports.company_staff.fields.phone'),
                'current' => $staffData['phone'] ?: __('reports.company_staff.phone_not_specified'),
                'format'  => fn ($val) => $val ?: __('reports.company_staff.phone_not_specified'),
            ],
            'fired' => [
                'label'   => __('reports.company_staff.fields.status'),
                'current' => $staffData['fired']
                    ? __('reports.company_staff.status.fired')
                    : __('reports.company_staff.status.working'),
                'format' => fn ($val) => $val
                    ? __('reports.company_staff.status.fired')
                    : __('reports.company_staff.status.working'),
            ],
        ];

        $lines = [
            "{$icon} {$header}",
            '',
            __('reports.company_staff.branch', ['branch' => $branchName]),
        ];

        foreach ($fields as $key => $config) {
            if (isset($changes[$key])) {
                $old = ($config['format'])($changes[$key]['old']);
                $new = ($config['format'])($changes[$key]['new']);
                $lines[] = "{$config['label']}: {$old} ➔ <b>{$new}</b>";
            } else {
                $lines[] = "{$config['label']}: {$config['current']}";
            }
        }

        return implode("\n", $lines);
    }
}
