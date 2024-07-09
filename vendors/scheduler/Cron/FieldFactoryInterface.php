<?php

namespace GO\Cron;

interface FieldFactoryInterface
{
    public function getField(int $position): FieldInterface;
}
