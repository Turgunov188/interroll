<?php

namespace App\Exceptions;

use App\Models\Lead;
use DomainException;

final class LeadAlreadyConverted extends DomainException
{
    public static function for(Lead $lead): self
    {
        return new self("Заявка {$lead->number} уже превращена в заказ.");
    }
}
