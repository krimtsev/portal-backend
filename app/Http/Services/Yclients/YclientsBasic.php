<?php

class YclientsBasic {
    /** Фиксированный токен приложения */
    private string $app_token;

    /** Фиксированный токен разработчика */
    private string $partner_token;

    public function __construct()
    {
        $this->app_token = config('services.yclients.app_token', '');
        $this->partner_token = config('services.yclients.partner_token', '');
    }

}
