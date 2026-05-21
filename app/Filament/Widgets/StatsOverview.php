<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // 📊 คำนวณหาจำนวนและยอดสะสมจากฐานข้อมูลแบบ Real-time
        $totalProducts = Product::count();
        $totalCustomers = Customer::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalPoints = Customer::sum('points_available') ?? 0;

        return [
            Stat::make('สินค้าในระบบ', number_format($totalProducts) . ' รายการ')
                ->description('รายการสินค้าทั้งหมดใน MongoDB')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('สมาชิกทั้งหมด', number_format($totalCustomers) . ' คน')
                ->description('ลูกค้าสะสมแต้มใน MongoDB')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('แต้มสะสมรวมในระบบ', number_format($totalPoints) . ' แต้ม')
                ->description('แต้มคงเหลือของลูกค้าทุกคน')
                ->descriptionIcon('heroicon-m-gift')
                ->color('warning'),

            Stat::make('ผู้ดูแลระบบ', number_format($totalAdmins) . ' คน')
                ->description('บัญชี Admin ที่ดูแลระบบใน SQLite')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),
        ];
    }
}
