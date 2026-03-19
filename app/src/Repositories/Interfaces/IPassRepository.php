<?php

namespace App\Repositories\Interfaces;

interface IPassRepository
{
    /**
     * @return array<int, array{event_id:int, festival_type:string, pass_scope:string, base_price:float, title:string, active:int}>
     */
    public function getActivePassProductsByFestivalType(string $festivalType): array;
}
