<?php

namespace App\Integrations\Telegram\Enums;

enum ParseMode: string
{
    case HTML = 'HTML';
    case MARKDOWN_V2 = 'MarkdownV2';
}
