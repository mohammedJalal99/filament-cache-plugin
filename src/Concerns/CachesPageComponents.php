<?php

namespace FilamentCache\Concerns;

use FilamentCache\CachesEverything;

trait CachesPageComponents
{
    use CachesEverything;

    protected function getActions(): array
    {
        return $this->cachePageActions(
            parent::getActions(),
            1800 // 30 minutes cache for page actions
        );
    }

    protected function getHeaderActions(): array
    {
        return $this->cachePageActions(
            parent::getHeaderActions(),
            1800
        );
    }

    protected function getFooterActions(): array
    {
        return $this->cachePageActions(
            parent::getFooterActions(),
            1800
        );
    }

    protected function getFormSchema(): array
    {
        return $this->cacheFormSchema(
            parent::getFormSchema(),
            1800
        );
    }

    // Cache page widgets
    protected function getHeaderWidgets(): array
    {
        return $this->cacheDashboardWidgets(
            parent::getHeaderWidgets(),
            300 // 5 minutes for widgets as they may contain dynamic data
        );
    }

    protected function getFooterWidgets(): array
    {
        return $this->cacheDashboardWidgets(
            parent::getFooterWidgets(),
            300
        );
    }

    // Cache info lists for view pages
    protected function getInfoLists(): array
    {
        return $this->cacheInfoLists(
            parent::getInfoLists(),
            1800
        );
    }
}
