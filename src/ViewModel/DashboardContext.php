<?php

namespace App\ViewModel;

final class DashboardContext
{
    /**
     * @param array<string, mixed> $dashboards
     */
    public function __construct(
        private readonly array $dashboards,
        private readonly bool $isAdmin
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboards(): array
    {
        return $this->dashboards;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}
