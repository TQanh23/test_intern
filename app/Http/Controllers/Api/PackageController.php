<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageIndexRequest;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PackageController extends Controller
{
    public function __construct(private PackageService $packageService) {}

    public function index(PackageIndexRequest $request): AnonymousResourceCollection
    {
        return PackageResource::collection(
            $this->packageService->paginate($request->integer('level') ?: null, $request->string('q')->value())
        );
    }

    public function show(Package $package): PackageResource
    {
        return new PackageResource($this->packageService->loadTableOfContents($package));
    }
}
