<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Enums\OrderStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** KPI tiles on the dashboard (respects the sales-manager scope of each resource). */
class CrmStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getColumns(): int|array|null
    {
        return ['default' => 2, 'md' => 3, 'xl' => 5];
    }

    protected function getStats(): array
    {
        $leads = LeadResource::getEloquentQuery();
        $orders = OrderResource::getEloquentQuery();

        $new = (clone $leads)->where('status', LeadStatus::New)->count();
        $today = (clone $leads)->whereDate('created_at', today())->count();
        $inWork = (clone $leads)->whereIn('status', [LeadStatus::InProgress, LeadStatus::Quoted])->count();

        $closed = [OrderStatus::Delivered->value, OrderStatus::Cancelled->value];
        $active = (clone $orders)->whereNotIn('status', $closed)->count();
        $overdue = (clone $orders)->whereNotIn('status', $closed)->whereDate('due_date', '<', today())->count();
        $ready = (clone $orders)->where('status', OrderStatus::Ready)->count();

        $monthLeads = (clone $leads)->where('created_at', '>=', now()->startOfMonth())->count();
        $monthWon = (clone $leads)->where('created_at', '>=', now()->startOfMonth())->where('status', LeadStatus::Won)->count();
        $conversion = $monthLeads > 0 ? round($monthWon / $monthLeads * 100) : 0;

        return [
            Stat::make('Новые заявки', $new)
                ->description("Сегодня: +{$today}")
                ->color($new > 0 ? 'warning' : 'gray')
                ->url(LeadResource::getUrl('index')),
            Stat::make('В работе', $inWork)
                ->description('Ждут ответа и КП')
                ->color('info'),
            Stat::make('Активные заказы', $active)
                ->description("Готово: {$ready}")
                ->color('primary')
                ->url(OrderResource::getUrl('index')),
            Stat::make('Просрочено', $overdue)
                ->description($overdue > 0 ? 'Проверьте срочно' : 'Всё в срок')
                ->color($overdue > 0 ? 'danger' : 'success'),
            Stat::make('Конверсия за месяц', $conversion.'%')
                ->description("{$monthWon} / {$monthLeads} заявок стали заказами")
                ->color('gray'),
        ];
    }
}
