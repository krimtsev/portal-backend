<?php

declare(strict_types=1);

namespace App\Exports\Reports;

use App\Models\Partner\Partner;
use App\Models\Yclients\YcRecord;
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
    public function generate(Partner $partner, Collection $items, string $fileName, string $reportType = ''): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(__('reports.clients.table.sheet_title'));

        $this->buildReportInfo($sheet, $partner, $reportType);
        $this->buildHeaders($sheet);
        $this->populateRows($sheet, $partner, $items);
        $this->autoFitColumns($sheet);

        return $this->saveToDisk($spreadsheet, $fileName);
    }

    private function buildReportInfo(Worksheet $sheet, Partner $partner, string $reportType): void
    {
        $sheet->setCellValue('A1', __('reports.clients.title') . ': ' . $reportType);
        $sheet->setCellValue('A2', __('reports.clients.date') . ': ' . now()->format('d.m.Y'));
        $sheet->setCellValue('A3', __('reports.clients.branch') . ': ' . $partner->name);
    }

    private function buildHeaders(Worksheet $sheet): void
    {
        $headers = [
            __('reports.clients.table.header.name'),
            __('reports.clients.table.header.phone'),
            __('reports.clients.table.header.date'),
            __('reports.clients.table.header.services'),
            __('reports.clients.table.header.partner'),
            __('reports.clients.table.header.other_branch_services'),
            __('reports.clients.table.header.link'),
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

        // Услуги основного филиала (выбранного)
        $mainServicesStr = $record->services
            ?->pluck('title')
            ->filter()
            ->implode(', ');

        $mainBranchServices = !empty($mainServicesStr)
            ? $mainServicesStr
            : __('reports.clients.table.no_data');

        // Услуги дополнительного филиала (если клиент ушел в другой филиал)
        $otherServicesStr = isset($item['other_branch_services'])
            ? $item['other_branch_services']->pluck('title')->filter()->implode(', ')
            : '';

        $otherBranchServices = !empty($otherServicesStr)
            ? $otherServicesStr
            : __('reports.clients.table.no_data');

        $branchName = $item['other_branch_name'] ?? $partner->name;

        $visitDate = ($item['other_branch_date'] ?? $record->datetime)?->format('Y-m-d H:i')
            ?? __('reports.clients.table.no_data');

        $sheet->setCellValue("A{$rowIndex}", $clientName);
        $sheet->setCellValue("B{$rowIndex}", $record->client_phone ?? __('reports.clients.table.no_data'));
        $sheet->setCellValue("C{$rowIndex}", $visitDate);
        $sheet->setCellValue("D{$rowIndex}", $mainBranchServices);
        $sheet->setCellValue("E{$rowIndex}", $branchName);
        $sheet->setCellValue("F{$rowIndex}", $otherBranchServices);

        if (!empty($record->client_id)) {
            $yclientsUrl = sprintf(
                'https://yclients.com/clients/%s/base/?#open_card_client_id=%s',
                $record->company_id,
                urlencode((string) $record->client_id)
            );

            $sheet->setCellValue("G{$rowIndex}", $yclientsUrl);
            $sheet->getCell("G{$rowIndex}")
                ->getHyperlink()
                ->setUrl($yclientsUrl)
                ->setTooltip(__('reports.clients.table.link_tooltip'));

            $sheet->getStyle("G{$rowIndex}")->applyFromArray([
                'font' => [
                    'color'     => ['rgb' => '0000FF'],
                    'underline' => true,
                ],
            ]);
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
