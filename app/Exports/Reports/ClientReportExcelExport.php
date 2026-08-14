<?php

declare(strict_types=1);

namespace App\Exports\Reports;

use App\Models\Partner\Partner;
use App\Models\Yclients\YcRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final readonly class ClientReportExcelExport
{
    /**
     * Генерирует XLSX файл и возвращает абсолютный путь к нему.
     *
     * @param  Collection<int, array{record: YcRecord, other_branch_name?: string, other_branch_date?: string}>  $items
     */
    public function generate(
        Partner $partner,
        Collection $items,
        string $fileName,
        string $reportType,
        int $days,
        Carbon $targetDate
    ): string {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('reports.clients.table.sheet_title'));

        $this->buildReportInfo($sheet, $partner, $reportType, $days, $targetDate);
        $this->buildHeaders($sheet);
        $this->populateRows($sheet, $partner, $items);
        $this->autoFitColumns($sheet);

        return $this->saveToDisk($spreadsheet, $fileName);
    }

    private function buildReportInfo(Worksheet $sheet, Partner $partner, string $reportType, int $days, Carbon $targetDate): void
    {
        $sheet->setCellValue('A1', __('reports.clients.title') . ': ' . $reportType);
        $sheet->setCellValue('A2', __('reports.clients.branch') . ': ' . $partner->name);

        $daysText = trans_choice('reports.clients.days', $days);
        $periodText = __('reports.clients.period', ['count' => $daysText]);

        $sheet->setCellValue('A3', __('reports.clients.date') . ': ' . $targetDate->format('Y-m-d') . ' (' . $periodText . ')');
    }

    private function buildHeaders(Worksheet $sheet): void
    {
        // Порядок колонок как на скриншоте
        $headers = [
            __('reports.clients.table.header.name'),
            __('reports.clients.table.header.phone'),
            __('reports.clients.table.header.services'),
            __('reports.clients.table.header.link'),
            __('reports.clients.table.header.partner'),
            __('reports.clients.table.header.date'),
            __('reports.clients.table.header.other_branch_services'),
        ];

        $sheet->fromArray($headers, null, 'A5');

        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
    }

    private function populateRows(Worksheet $sheet, Partner $partner, Collection $items): void
    {
        $rowIndex = 6;

        foreach ($items as $item) {
            $this->writeRow($sheet, $rowIndex, $partner, $item);
            $rowIndex++;
        }

        $lastRow = $rowIndex - 1;
        $sheet->getStyle("A5:G{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    private function writeRow(Worksheet $sheet, int $rowIndex, Partner $partner, array $item): void
    {
        /** @var YcRecord $record */
        $record = $item['record'];

        $clientName = $record->client_name
            ?? $record->client['name']
            ?? __('reports.clients.table.no_name');

        // Услуги основного филиала
        $mainServicesStr = $record->services
            ?->pluck('title')
            ->filter()
            ->implode(', ');

        $mainBranchServices = !empty($mainServicesStr)
            ? $mainServicesStr
            : __('reports.clients.table.no_data');

        // 1. Название филиала (выводим только если был визит в другой филиал)
        $branchName = $item['other_branch_name'] ?? __('reports.clients.table.no_data');

        // 2. Дата посещения (выводим только если был визит в другой филиал)
        $visitDate = isset($item['other_branch_date'])
            ? $item['other_branch_date']?->format('Y-m-d H:i')
            : __('reports.clients.table.no_data');

        // 3. Услуги в другом филиале (выводим только если они есть)
        $otherServicesStr = isset($item['other_branch_services'])
            ? $item['other_branch_services']->pluck('title')->filter()->implode(', ')
            : '';

        $otherBranchServices = !empty($otherServicesStr)
            ? $otherServicesStr
            : __('reports.clients.table.no_data');

        // Заполнение ячеек
        $sheet->setCellValue("A{$rowIndex}", $clientName);
        $sheet->setCellValue("B{$rowIndex}", $record->client_phone ?? __('reports.clients.table.no_data'));
        $sheet->setCellValue("C{$rowIndex}", $mainBranchServices);
        $sheet->setCellValue("E{$rowIndex}", $branchName);
        $sheet->setCellValue("F{$rowIndex}", $visitDate);
        $sheet->setCellValue("G{$rowIndex}", $otherBranchServices);

        if (!empty($record->client_id)) {
            $yclientsUrl = sprintf(
                'https://yclients.com/clients/%s/base/?#open_card_client_id=%s',
                $record->company_id,
                urlencode((string) $record->client_id)
            );

            $sheet->setCellValue("D{$rowIndex}", 'Карточка клиента');
            $sheet->getCell("D{$rowIndex}")
                ->getHyperlink()
                ->setUrl($yclientsUrl)
                ->setTooltip(__('reports.clients.table.link_tooltip'));

            $sheet->getStyle("D{$rowIndex}")->applyFromArray([
                'font' => [
                    'color'     => ['rgb' => '0000FF'],
                    'underline' => true,
                ],
            ]);
        } else {
            $sheet->setCellValue("D{$rowIndex}", __('reports.clients.table.no_data'));
        }
    }

    private function autoFitColumns(Worksheet $sheet): void
    {
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function saveToDisk(Spreadsheet $spreadsheet, string $fileName): string
    {
        $disk = Storage::disk('reports');

        $disk->makeDirectory(dirname($fileName));
        $fullPath = $disk->path($fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        return $fullPath;
    }
}
