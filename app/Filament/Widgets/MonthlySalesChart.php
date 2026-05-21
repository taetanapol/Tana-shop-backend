<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Carbon\Carbon;

class MonthlySalesChart extends ChartWidget
{
    protected ?string $heading = 'ยอดขายรายเดือน';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // 📊 Query sales data from MongoDB for the current year
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        $orders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->get(['total_price', 'created_at']);

        // Initialize array for 12 months (1 to 12)
        $monthlySales = array_fill(1, 12, 0.0);

        foreach ($orders as $order) {
            $date = $order->created_at;
            if ($date) {
                $month = Carbon::parse($date)->month;
                $monthlySales[$month] += (float) ($order->total_price ?? 0);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'ยอดขาย (บาท)',
                    'data' => array_values($monthlySales),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)', // translucent amber
                    'borderColor' => '#f59e0b', // amber border
                    'borderWidth' => 2,
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
            ],
            'labels' => [
                'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
