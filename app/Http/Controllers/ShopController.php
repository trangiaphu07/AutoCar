<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarAttributeValue;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    private const PRICE_SQL = 'CASE WHEN sale_price IS NOT NULL AND sale_price < price THEN sale_price ELSE price END';

    public const PER_PAGE = [9, 18, 36];

    public const PRICE_RANGES = [
        'duoi-500' => ['Dưới 500 triệu', 0, 500_000_000],
        '500-800' => ['500 – 800 triệu', 500_000_000, 800_000_000],
        '800-1200' => ['800 triệu – 1,2 tỷ', 800_000_000, 1_200_000_000],
        'tren-1200' => ['Trên 1,2 tỷ', 1_200_000_000, null],
    ];

    public function home()
    {
        $base = Car::published()->with(['brand', 'category', 'attributeValues.attribute']);

        return view('shop.home', [
            'featured' => (clone $base)->where('is_featured', true)->where('stock', '>', 0)->latest('id')->take(6)->get(),
            'latest' => (clone $base)->where('stock', '>', 0)->latest('id')->take(8)->get(),
            'deals' => (clone $base)->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')->take(4)->get(),
            // Mỗi dòng xe kèm tối đa 8 xe còn hàng để hiển thị theo tab ở trang chủ
            'categories' => Category::withCount(['cars' => fn ($q) => $q->published()])
                ->with(['cars' => fn ($q) => $q->published()->where('stock', '>', 0)->with(['brand', 'category', 'attributeValues.attribute'])->latest('id')->limit(8)])
                ->orderBy('sort_order')->get(),
            'brands' => Brand::withCount(['cars' => fn ($q) => $q->published()])->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request)
    {
        return $this->listing($request, null);
    }

    public function category(Request $request, Category $category)
    {
        return $this->listing($request, $category);
    }

    private function listing(Request $request, ?Category $category)
    {
        $query = Car::published()->with(['brand', 'category', 'attributeValues.attribute']);

        if ($category) {
            $query->where('category_id', $category->id);
        }

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function (Builder $w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$q}%"));
            });
        }

        if ($brands = array_filter((array) $request->query('brand'))) {
            $query->whereHas('brand', fn ($b) => $b->whereIn('slug', $brands));
        }

        if ($cond = $request->query('condition')) {
            if (array_key_exists($cond, Car::CONDITIONS)) {
                $query->where('condition', $cond);
            }
        }

        if (($range = $request->query('price')) && isset(self::PRICE_RANGES[$range])) {
            [, $min, $max] = self::PRICE_RANGES[$range];
            $query->whereRaw(self::PRICE_SQL.' >= ?', [$min]);
            if ($max) {
                $query->whereRaw(self::PRICE_SQL.' < ?', [$max]);
            }
        }

        if ($yearFrom = (int) $request->query('year_from')) {
            $query->where('year', '>=', $yearFrom);
        }

        // Bộ lọc động theo thông số "có thể lọc" của dòng xe
        $filterAttributes = collect();
        if ($category) {
            $filterAttributes = $category->attributes()->where('is_filterable', true)->get();
            foreach ($filterAttributes as $attr) {
                $chosen = array_filter((array) data_get($request->query('attr'), $attr->code));
                if ($chosen) {
                    $query->whereHas('attributeValues', fn ($v) => $v->where('attribute_id', $attr->id)->whereIn('value', $chosen));
                }
                // Giá trị lọc lấy từ chính dữ liệu đang bán
                $attr->available = CarAttributeValue::where('attribute_id', $attr->id)
                    ->whereHas('car', fn ($c) => $c->published())
                    ->select('value', DB::raw('count(*) as total'))
                    ->groupBy('value')->orderBy('value')->get();
            }
        }

        match ($request->query('sort')) {
            'price_asc' => $query->orderByRaw(self::PRICE_SQL.' asc'),
            'price_desc' => $query->orderByRaw(self::PRICE_SQL.' desc'),
            'year_desc' => $query->orderByDesc('year')->orderByDesc('id'),
            'popular' => $query->orderByDesc('views'),
            default => $query->latest('id'),
        };

        $perPage = in_array((int) $request->query('per_page'), self::PER_PAGE, true) ? (int) $request->query('per_page') : self::PER_PAGE[0];

        return view('shop.index', [
            'cars' => $query->paginate($perPage)->withQueryString(),
            'category' => $category,
            'categories' => Category::orderBy('sort_order')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'filterAttributes' => $filterAttributes,
            'perPageOptions' => self::PER_PAGE,
            'priceRanges' => self::PRICE_RANGES,
        ]);
    }

    public function show(string $slug)
    {
        $car = Car::published()->where('slug', $slug)
            ->with(['brand', 'category.attributes', 'attributeValues.attribute'])
            ->firstOrFail();

        $car->increment('views');

        $related = Car::published()->with(['brand', 'category', 'attributeValues.attribute'])
            ->where('id', '!=', $car->id)
            ->where(fn ($q) => $q->where('category_id', $car->category_id)->orWhere('brand_id', $car->brand_id))
            ->inRandomOrder()->take(4)->get();

        $values = $car->attributeValues->keyBy('attribute_id');
        $specs = $car->category->attributes->map(fn (Attribute $a) => [
            'label' => $a->label(),
            'value' => $values->get($a->id)?->value,
        ])->filter(fn ($s) => filled($s['value']));

        return view('shop.show', compact('car', 'related', 'specs'));
    }

    /* ---------- So sánh xe (tối đa 3 xe, lưu trong session) ---------- */

    public function toggleCompare(Car $car)
    {
        $list = session('compare', []);
        if (in_array($car->id, $list, true)) {
            $list = array_values(array_diff($list, [$car->id]));
            $msg = "Đã bỏ “{$car->name}” khỏi danh sách so sánh.";
        } else {
            if (count($list) >= 3) {
                return back()->with('error', 'Chỉ so sánh tối đa 3 xe cùng lúc.');
            }
            $list[] = $car->id;
            $msg = "Đã thêm “{$car->name}” vào danh sách so sánh.";
        }
        session(['compare' => $list]);

        return back()->with('success', $msg);
    }

    public function compare()
    {
        $ids = session('compare', []);
        $cars = Car::published()->with(['brand', 'category', 'attributeValues.attribute'])->whereIn('id', $ids)->get();

        // Gộp tất cả thông số của các xe đang so sánh
        $specNames = $cars->flatMap(fn ($c) => $c->attributeValues->map(fn ($v) => $v->attribute->label()))->unique()->values();

        return view('shop.compare', compact('cars', 'specNames'));
    }
}
