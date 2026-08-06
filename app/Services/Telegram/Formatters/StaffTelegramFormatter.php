<?php

declare(strict_types=1);

namespace App\Services\Telegram\Formatters;

final class StaffTelegramFormatter
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
        $phone = $staffData['phone'] ?: 'Не указан';
        $specialization = $staffData['specialization'] ?: 'Не указана';
        $name = $staffData['name'];

        $message = "Добавлен новый сотрудник:\n\n";
        $message .= "Филиал: {$branchName}\n";
        $message .= "Имя: {$name}\n";
        $message .= "Специализация: {$specialization}\n";
        $message .= "Телефон: {$phone}";

        return $message;
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
        $fields = [
            'name' => [
                'label'   => 'Имя',
                'current' => $staffData['name'],
                'format'  => fn ($val) => $val ?: 'Не указано',
            ],
            'specialization' => [
                'label'   => 'Специализация',
                'current' => $staffData['specialization'] ?: 'Не указана',
                'format'  => fn ($val) => $val ?: 'Не указана',
            ],
            'phone' => [
                'label'   => 'Телефон',
                'current' => $staffData['phone'] ?: 'Не указан',
                'format'  => fn ($val) => $val ?: 'Не указан',
            ],
            'fired' => [
                'label'   => 'Статус',
                'current' => $staffData['fired'] ? 'Уволен' : 'Работает',
                'format'  => fn ($val) => $val ? 'Уволен' : 'Работает',
            ],
        ];

        $lines = [
            "Изменены данные сотрудника:\n",
            "Филиал: {$branchName}",
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
