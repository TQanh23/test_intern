<?php

namespace App\Http\Resources;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Package
 */
class PackageResource extends JsonResource
{
    /**
     * Danh sách không load units nên không có khóa `units`; trang chi tiết thì có.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'level' => $this->level,
            'price' => $this->price,
            'cover_url' => $this->cover_image ? asset($this->cover_image) : null,
            'description' => $this->description,
            // Chỉ danh sách gọi withCount('documents'); trang chi tiết đếm từ units.
            'documents_count' => $this->whenCounted('documents'),
            'units' => UnitResource::collection($this->whenLoaded('units')),
        ];
    }
}
