<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

use App\Enums\Report\ClientReportType;

final class ProcessLostClientsReportJob extends AbstractClientReportJob
{
    protected function reportType(): ClientReportType
    {
        return ClientReportType::LOST_CLIENTS;
    }
}
