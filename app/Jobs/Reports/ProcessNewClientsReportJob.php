<?php

declare(strict_types=1);

namespace App\Jobs\Reports;

use App\Enums\Report\ClientReportType;

final class ProcessNewClientsReportJob extends AbstractClientReportJob
{
    protected function reportType(): ClientReportType
    {
        return ClientReportType::NEW_CLIENTS;
    }
}
