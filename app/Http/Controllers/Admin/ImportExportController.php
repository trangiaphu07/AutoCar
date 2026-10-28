<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Nhập/xuất xe bằng CSV (UTF-8, có BOM để mở bằng Excel không lỗi font).
 * Cột: sku,name,category,brand,year,condition,mileage,color,price,sale_price,stock,status,short_description,attributes
 * attributes: ma_thong_so=gia_tri;ma_khac=gia_tri
 */
class ImportExportController extends Controller
{
    public const COLUMNS = ['sku', 'name', 'category', 'brand', 'year', 'condition', 'mileage', 'color', 'price', 'sale_price', 'stock', 'status', 'short_description', 'attributes'];

    public function index()
    {
        return view('admin.import-export', ['columns' => self::COLUMNS]);
    }

    public function export()
    {
        $filename = 'autoshop-xe-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::COLUMNS);

            Car::with(['category', 'brand', 'attributeValues.attribute'])->orderBy('id')->chunk(200, function ($cars) use ($out) {
                foreach ($cars as $car) {
                    fputcsv($out, [
                        $car->sku, $car->name, $car->category?->name, $car->brand?->name, $car->year, $car->condition,
                        $car->mileage, $car->color, $car->price, $car->sale_price, $car->stock, $car->status,
                        $car->short_description,
                        $car->attributeValues->map(fn ($v) => $v->attribute->code.'='.$v->value)->implode(';'),
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        if (! $header) {
            return back()->with('error', 'Tệp rỗng.');
        }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        foreach (['sku', 'name', 'category', 'price'] as $col) {
            if (! in_array($col, $header, true)) {
                return back()->with('error', "Thiếu cột bắt buộc “{$col}”.");
            }
        }

        $created = $updated = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($row, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;
            }
            $r = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), ''));
            $r = array_map(fn ($v) => trim((string) $v), $r);

            if ($r['sku'] === '' || $r['name'] === '') {
                $errors[] = "Dòng {$line}: thiếu SKU hoặc tên xe.";

                continue;
            }
            $category = Category::where('name', $r['category'])->orWhere('slug', Str::slug($r['category']))->first();
            if (! $category) {
                $errors[] = "Dòng {$line}: không có dòng xe “{$r['category']}”.";

                continue;
            }
            if (! ctype_digit($r['price']) || (int) $r['price'] <= 0) {
                $errors[] = "Dòng {$line}: giá không hợp lệ.";

                continue;
            }

            DB::transaction(function () use ($r, $category, &$created, &$updated) {
                $brand = filled($r['brand'] ?? '')
                    ? Brand::firstOrCreate(['slug' => Str::slug($r['brand'])], ['name' => $r['brand']])
                    : null;

                $car = Car::withTrashed()->where('sku', $r['sku'])->first();
                $isNew = ! $car;
                $car ??= new Car(['sku' => $r['sku']]);

                $year = (int) ($r['year'] ?? 0) ?: (int) date('Y');
                $status = in_array($r['status'] ?? '', array_keys(Car::STATUSES), true) ? $r['status'] : 'draft';

                $car->fill([
                    'category_id' => $category->id,
                    'brand_id' => $brand?->id,
                    'name' => $r['name'],
                    'slug' => $car->slug ?: $this->uniqueSlug($r['name'].'-'.$year),
                    'year' => $year,
                    'condition' => ($r['condition'] ?? '') === 'used' ? 'used' : 'new',
                    'mileage' => (int) ($r['mileage'] ?? 0),
                    'color' => ($r['color'] ?? '') ?: null,
                    'price' => (int) $r['price'],
                    'sale_price' => ctype_digit($r['sale_price'] ?? '') && (int) $r['sale_price'] < (int) $r['price'] ? (int) $r['sale_price'] : null,
                    'stock' => (int) ($r['stock'] ?? 0),
                    'status' => $status,
                    'short_description' => ($r['short_description'] ?? '') ?: $car->short_description,
                ]);
                $car->deleted_at = null;
                $car->save();

                foreach (array_filter(explode(';', $r['attributes'] ?? '')) as $pair) {
                    [$code, $value] = array_pad(explode('=', $pair, 2), 2, '');
                    $attr = Attribute::where('category_id', $category->id)->where('code', trim($code))->first();
                    if ($attr && trim($value) !== '') {
                        $car->attributeValues()->updateOrCreate(['attribute_id' => $attr->id], ['value' => trim($value)]);
                    }
                }

                // Thiếu thông số bắt buộc -> không cho ở trạng thái Đang bán
                if ($car->status === 'published' && $car->missingRequiredAttributes()) {
                    $car->forceFill(['status' => 'review'])->saveQuietly();
                }
                $car->refreshCompleteness();

                $isNew ? $created++ : $updated++;
            });
        }
        fclose($handle);

        return back()->with('success', "Nhập xong: {$created} xe mới, {$updated} xe cập nhật.")->with('import_errors', $errors);
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $n = 2;
        while (Car::withTrashed()->where('slug', $slug)->exists()) {
            $slug = Str::slug($base).'-'.$n++;
        }

        return $slug;
    }
}
