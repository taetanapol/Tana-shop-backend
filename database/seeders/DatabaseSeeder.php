<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed/Update admin user for local development access
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Create mock customers in MongoDB if none exist
        if (Customer::count() === 0) {
            $customerNames = ['สมชาย ใจดี', 'สมหญิง รักดี', 'สมศักดิ์ ขยัน', 'วิชัย ร่ำรวย', 'นารี มีสุข'];
            foreach ($customerNames as $index => $name) {
                Customer::create([
                    'name' => $name,
                    'email' => "customer{$index}@example.com",
                    'phone' => '081234567' . $index,
                    'points_available' => rand(100, 1000),
                    'points_used' => rand(0, 200),
                    'points_total_earned' => rand(500, 1200),
                    'point_history' => [],
                ]);
            }
        }

        // 3. Clear existing orders and generate fresh mock orders for the current calendar year
        Order::truncate();

        $customers = Customer::all();

        if ($customers->count() > 0) {
            $currentYear = Carbon::now()->year;

            // Seed mock orders for each of the 12 months
            for ($month = 1; $month <= 12; $month++) {
                // Vary order count per month to show a beautiful chart trend
                $orderCount = match ($month) {
                    1, 2 => rand(5, 10),
                    3, 4 => rand(8, 15),
                    5, 6, 7 => rand(12, 22),
                    8, 9, 10 => rand(18, 30),
                    11, 12 => rand(25, 45), // Peak season
                    default => 10,
                };

                for ($i = 0; $i < $orderCount; $i++) {
                    $customer = $customers->random();
                    $createdDate = Carbon::create($currentYear, $month, rand(1, 28), rand(9, 21), rand(0, 59));

                    // Random price between 100 and 5000
                    $totalPrice = rand(150, 4800) + (rand(0, 9) / 10);

                    // Random status: completed (80%), pending (15%), cancelled (5%)
                    $randStatusVal = rand(1, 100);
                    $status = 'completed';
                    if ($randStatusVal > 80 && $randStatusVal <= 95) {
                        $status = 'pending';
                    } elseif ($randStatusVal > 95) {
                        $status = 'cancelled';
                    }

                    Order::create([
                        'customer_id' => $customer->_id,
                        'total_price' => $totalPrice,
                        'status' => $status,
                        'created_at' => $createdDate,
                        'updated_at' => $createdDate,
                    ]);
                }
            }
        }
    }
}
