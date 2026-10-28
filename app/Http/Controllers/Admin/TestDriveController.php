<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestDrive;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestDriveController extends Controller
{
    public function index(Request $request)
    {
        $query = TestDrive::with('car')->orderByRaw("CASE status WHEN 'new' THEN 0 WHEN 'confirmed' THEN 1 ELSE 2 END")
            ->orderBy('preferred_date');

        if ($s = $request->query('status')) {
            $query->where('status', $s);
        }

        return view('admin.test-drives.index', ['drives' => $query->paginate(20)->withQueryString()]);
    }

    public function update(Request $request, TestDrive $testDrive)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(TestDrive::STATUSES))]]);
        $testDrive->update($data);

        return back()->with('success', 'Đã cập nhật lịch lái thử của '.$testDrive->name.'.');
    }
}
