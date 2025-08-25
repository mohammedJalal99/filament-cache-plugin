<?php

namespace FilamentCache\Concerns;

use FilamentCache\CachesEverything;

trait CachesWidgetComponents
{
    use CachesEverything;

    // Cache widget data with shorter TTL since widgets often show real-time data
    protected function getData(): array
    {
        return $this->cacheWidgetData(
            parent::getData(),
            300 // 5 minutes cache for widget data
        );
    }

    // Cache chart data
    protected function getOptions(): array
    {
        return $this->cacheOptions(
            'chart_options_' . static::class,
            fn() => parent::getOptions(),
            900 // 15 minutes for chart options
        );
    }

    // Cache stats overview data
    protected function getCards(): array
    {
        return $this->cacheWidgetData(
            parent::getCards(),
            180 // 3 minutes for stats cards
        );
    }

    // Cache table data for table widgets
    protected function getTableQuery()
    {
        $query = parent::getTableQuery();
        return $this->cacheTableQuery($query, [], '', 120); // 2 minutes for table widgets
    }

    // Cache widget filters
    protected function getFilters(): ?array
    {
        return $this->cacheOptions(
            'widget_filters_' . static::class,
            fn() => parent::getFilters(),
            1800
        );
    }
}
