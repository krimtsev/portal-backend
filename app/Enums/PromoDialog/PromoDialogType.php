<?php

namespace App\Enums\PromoDialog;

enum PromoDialogType: string
{
    /** Подготовленный шаблон диалога на фронте */
    case Template = 'template';

    /** Произвольный диалог: показываем заголовок и описание из БД */
    case Custom = 'custom';
}
