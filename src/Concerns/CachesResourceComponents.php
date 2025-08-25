<?php

namespace FilamentCache\Concerns;

use FilamentCache\CachesEverything;

trait CachesResourceComponents
{
    use CachesEverything;

    protected function getFormSchema(string $context = 'create'): array
    {
        return $this->cacheFormSchema(
            parent::getFormSchema($context),
            1800 // 30 minutes cache for form schemas
        );
    }

    protected function getTableColumns(): array
    {
        return $this->cacheTableColumns(
            parent::getTableColumns(),
            1800 // 30 minutes cache
        );
    }

    protected function getTableFilters(): array
    {
        return $this->cacheTableFilters(
            parent::getTableFilters(),
            1800
        );
    }

    protected function getTableActions(): array
    {
        return $this->cacheTableActions(
            parent::getTableActions(),
            1800
        );
    }

    protected function getTableBulkActions(): array
    {
        return $this->cacheBulkActions(
            parent::getTableBulkActions(),
            1800
        );
    }

    protected function getRelationManagers(): array
    {
        return $this->cacheRelationManagers(
            parent::getRelationManagers(),
            3600 // 1 hour cache for relation managers
        );
    }

    protected function getPages(): array
    {
        return $this->cacheResourcePages(
            parent::getPages(),
            3600 // 1 hour cache for pages
        );
    }

    protected function getTableQuery()
    {
        $query = parent::getTableQuery();

        // Cache the query results based on current filters and search
        $filters = $this->getTableFilters();
        $search = request('tableSearch', '');

        return $this->cacheTableQuery($query, $filters, $search, 60); // 1 minute cache for data
    }

    // Override navigation methods to use caching
    protected function getNavigationItems(): array
    {
        return $this->cacheNavigationItems(
            parent::getNavigationItems(),
            3600
        );
    }

    // Cache permissions for better performance
    protected function canViewAny(): bool
    {
        $permissions = $this->cacheUserPermissions();
        return $permissions['can_view'];
    }

    protected function canCreate(): bool
    {
        $permissions = $this->cacheUserPermissions();
        return $permissions['can_create'];
    }

    protected function canEdit($record): bool
    {
        $permissions = $this->cacheUserPermissions();
        return $permissions['can_update'];
    }

    protected function canDelete($record): bool
    {
        $permissions = $this->cacheUserPermissions();
        return $permissions['can_delete'];
    }
}
