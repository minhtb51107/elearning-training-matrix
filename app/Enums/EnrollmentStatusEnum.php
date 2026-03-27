<?php

namespace App\Enums;

enum EnrollmentStatusEnum: string
{
    case ENROLLED = 'enrolled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}