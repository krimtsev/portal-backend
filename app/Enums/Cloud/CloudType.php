<?php

namespace App\Enums\Cloud;

enum CloudType: string
{
    case FOLDER = 'folder';
    case FILE = 'file';
}
