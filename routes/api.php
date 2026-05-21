<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/{collection}', function (Request $request, $collection) {
    // 1. แปลงชื่อ URL เช่น /api/products ให้กลายเป็นชื่อ Model (Product)
    $modelName = 'App\\Models\\' . Str::studly(Str::singular($collection));

    // ตรวจสอบความปลอดภัยว่ามีคลาส Model นี้อยู่ในเครื่องจริงไหม
    if (!class_exists($modelName)) {
        return response()->json(['message' => "ไม่พบ Collection ที่ชื่อ [{$collection}] ในระบบ"], 404);
    }

    // 2. ดึงค่าพารามิเตอร์การแบ่งหน้า (Limit) แบบเดียวกับ Payload
    $limit = $request->query('limit', 10);

    // 3. เปิดท่อดึงข้อมูลออโต้สวนกลับไปทันที (รองรับทั้ง MySQL และ MongoDB ของพี่)
    $data = $modelName::latest()->paginate($limit);

    return response()->json($data);
});

Route::get('/{collection}/{id}', function ($collection, $id) {
    $modelName = 'App\\Models\\' . Str::studly(Str::singular($collection));

    if (!class_exists($modelName)) {
        return response()->json(['message' => 'ไม่พบข้อมูล'], 404);
    }

    // สั่งขุดหารายชิ้นออโต้
    $item = $modelName::findOrFail($id);

    return response()->json(['data' => $item]);
});
