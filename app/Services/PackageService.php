<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PackageService
{
    /**
     * Danh sách gói, lọc theo level nếu có.
     *
     * @return LengthAwarePaginator<int, Package>
     */
    public function paginate(?int $level): LengthAwarePaginator
    {
        return Package::query()
            ->when($level, fn ($query) => $query->where('level', $level))
            ->orderBy('level')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Mục lục: unit theo position, tài liệu theo thứ tự tạo. Hai query cho cả cây, không N+1.
     */
    public function loadTableOfContents(Package $package): Package
    {
        return $package->load([
            'units' => fn ($query) => $query->orderBy('position'),
            'units.documents' => fn ($query) => $query->orderBy('id'),
        ]);
    }
}
