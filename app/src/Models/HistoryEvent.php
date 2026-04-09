<?php

declare(strict_types=1);

namespace App\Models;

class HistoryEvent extends GenericEvent
{
    public string $language;
    public string $start_date;

    public function __construct(array $row)
    {
        parent::__construct($row);

        if ($this->event_type === '') {
            $this->event_type = 'history';
        }

        $this->language = strtoupper((string)($row['language'] ?? ''));
        $this->start_date = (string)($row['start_date'] ?? '');
    }
}