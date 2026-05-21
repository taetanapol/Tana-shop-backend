<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerPointController extends Controller
{
    /**
     * 📊 1. API ดึงดูพ้อยท์ 3 สถานะ (เอาไปโชว์หน้าบ้านได้ทันที)
     */
    public function getPointSummary(Request $request)
    {
        $customer = $request->user(); // ดึงข้อมูลลูกค้าที่ล็อกอินอยู่

        return response()->json([
            'customer_name' => $customer->name,
            'points' => [
                'available' => (int) ($customer->points_available ?? 0), // 🟢 พ้อยท์คงเหลือที่ใช้ได้
                'used' => (int) ($customer->points_used ?? 0),           // 🔴 พ้อยท์ที่ใช้ไปแล้วทั้งหมด
                'total_earned' => (int) ($customer->points_total_earned ?? 0), // 🔵 พ้อยท์รวมสะสมตั้งแต่สมัครเว็บ
            ],
            'point_history' => $customer->point_history ?? [] // 📜 ประวัติการรับและใช้แต้มแบบละเอียด
        ]);
    }

    /**
     * ➕ 2. API สะสมแต้มเพิ่ม (เช่น ยิงทำงานหลังหน้าบ้านจ่ายเงินเสร็จ)
     */
    public function earnPoints(Request $request)
    {
        $request->validate(['amount' => 'required|integer|min:1']);
        $customer = $request->user();
        $earnAmount = $request->amount;

        // คำนวณอัปเดตแต้ม
        $customer->points_available = ($customer->points_available ?? 0) + $earnAmount;
        $customer->points_total_earned = ($customer->points_total_earned ?? 0) + $earnAmount;

        // ฝังบันทึกประวัติ (Log) เข้า Array ใน MongoDB
        $history = $customer->point_history ?? [];
        $history[] = [
            'transaction_id' => uniqid('TXN_'),
            'type' => 'earn', // ได้รับแต้ม
            'amount' => $earnAmount,
            'description' => "ได้รับคะแนนสะสมจากการซื้อสินค้า",
            'created_at' => now()->toIso8601String()
        ];

        $customer->point_history = $history;
        $customer->save();

        return response()->json(['message' => 'สะสมแต้มสำเร็จ', 'points_available' => $customer->points_available]);
    }

    /**
     * ➖ 3. API หักพ้อยท์/แลกคะแนน
     */
    public function redeemPoints(Request $request)
    {
        $request->validate([
            'amount_to_use' => 'required|integer|min:1',
            'reward_name' => 'required|string'
        ]);

        $customer = $request->user();
        $useAmount = $request->amount_to_use;

        // ตรวจสอบว่าพ้อยท์คงเหลือ (Available) พอให้หักไหม
        if (($customer->points_available ?? 0) < $useAmount) {
            return response()->json(['message' => 'คะแนนสะสมของคุณไม่เพียงพอครับพี่'], 400);
        }

        // คำนวณตัดยอดแต้ม
        $customer->points_available -= $useAmount; // หักแต้มคงเหลือ
        $customer->points_used = ($customer->points_used ?? 0) + $useAmount; // โยกแต้มไปสะสมที่ช่อง "ใช้ไปแล้ว"

        // ฝังบันทึกประวัติการใช้แต้ม
        $history = $customer->point_history ?? [];
        $history[] = [
            'transaction_id' => uniqid('TXN_'),
            'type' => 'redeem', // ใช้แต้ม
            'amount' => $useAmount,
            'description' => "ใช้แต้มแลกรางวัล: " . $request->reward_name,
            'created_at' => now()->toIso8601String()
        ];

        $customer->point_history = $history;
        $customer->save();

        return response()->json(['message' => 'ใช้คะแนนแลกรางวัลสำเร็จแล้วครับพี่!']);
    }
}
