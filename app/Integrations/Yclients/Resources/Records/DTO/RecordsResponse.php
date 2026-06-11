<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\BaseResponse;

class RecordsResponse extends BaseResponse
{
    /** @var ServiceDTO[]|null */
    private ?array $_services = null;

    /** @var ClientDTO|null */
    private ?ClientDTO $_client = null;

    public function __construct(
        public int $id,
        public int $company_id,
        public int $staff_id,
        public int $visit_id,
        protected array $services,
        public array $staff,
        public array $datetime,
        protected array $client,
    ) {}

    /**
     * * @return ServiceDTO[]
     */
    public function services(): array
    {
        if ($this->_services === null) {
            $this->_services = array_map(
                fn(array $service) => new ServiceDTO(...$service),
                $this->services
            );
        }
        return $this->_services;
    }

    public function client(): ?ClientDTO
    {
        if ($this->_client === null) {
            $this->_client = !empty($this->client)
                ? new ClientDTO(...$this->client)
                : null;
        }
        return $this->_client;
    }

    protected static function getInputMapping(): array
    {
        return [
            'id'         => 'data.id',
            'company_id' => 'data.company_id',
            'staff_id'   => 'data.staff_id',
            'services'   => 'data.services',
            'staff'      => 'data.staff',
            'datetime'   => 'data.datetime',
            'client'     => 'data.client',
        ];
    }
}
