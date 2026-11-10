{{--
    Modal xem thử PDF/audio. Mở bằng event `preview-document` (detail là document từ API), không reload trang.
    Đặt một lần trên trang; logic ở resources/js/ielts/preview-modal.js.
--}}
<x-modal name="document-preview" maxWidth="2xl">
    <div x-data="previewModal" x-on:preview-document.window="open($event.detail)">
        <div class="flex items-center gap-3 bg-surface-low px-4 py-3 sm:px-6">
            <x-ielts.file-icon />
            <div class="min-w-0 flex-1">
                <h2 class="truncate font-semibold text-ink" x-text="doc?.title"></h2>
                <p class="truncate text-xs text-slate-500" x-text="doc ? describeDocument(doc) : ''"></p>
            </div>
            <button type="button" x-on:click="show = false" aria-label="Đóng"
                class="rounded-lg p-2 text-slate-400 hover:bg-surface-low hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <x-async-state retry="retry()" class="p-4 sm:p-6">
            <x-slot name="loading">
                <div class="flex flex-col items-center justify-center gap-3 py-16 text-slate-500">
                    <svg class="h-8 w-8 animate-spin text-brand" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25" />
                        <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
                    </svg>
                    <span>Đang mở bản xem thử...</span>
                </div>
            </x-slot>

            {{-- PDF --}}
            <div x-show="doc?.type === 'pdf'" class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-1">
                        <button type="button" x-on:click="prev()" :disabled="page === 1 && !limitReached" aria-label="Trang trước"
                            class="rounded-lg p-2 text-slate-600 ring-1 ring-surface-high hover:bg-surface disabled:cursor-not-allowed disabled:opacity-40">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        </button>
                        <span class="min-w-[6rem] px-2 text-center text-sm tabular-nums text-slate-600" aria-live="polite"
                            x-text="'Trang ' + (limitReached ? previewPages + 1 : page) + ' / ' + (doc?.page_count ?? previewPages)"></span>
                        <button type="button" x-on:click="next()" :disabled="limitReached || (page === previewPages && doc?.page_count <= previewPages)" aria-label="Trang sau"
                            class="rounded-lg p-2 text-slate-600 ring-1 ring-surface-high hover:bg-surface disabled:cursor-not-allowed disabled:opacity-40">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" x-on:click="zoom(-1)" :disabled="scale <= minScale" aria-label="Thu nhỏ"
                            class="h-9 w-9 rounded-lg text-lg font-semibold text-slate-600 ring-1 ring-surface-high hover:bg-surface disabled:opacity-40">−</button>
                        <span class="w-14 text-center text-sm tabular-nums text-slate-600" x-text="zoomLabel"></span>
                        <button type="button" x-on:click="zoom(1)" :disabled="scale >= maxScale" aria-label="Phóng to"
                            class="h-9 w-9 rounded-lg text-lg font-semibold text-slate-600 ring-1 ring-surface-high hover:bg-surface disabled:opacity-40">+</button>
                    </div>
                </div>

                <div x-ref="viewer" class="relative max-h-[65vh] overflow-auto rounded-xl bg-surface-low p-4">
                    <canvas x-ref="canvas" x-show="!limitReached" class="mx-auto block bg-white shadow"></canvas>

                    <div x-show="limitReached" class="flex flex-col items-center gap-3 px-4 py-16 text-center">
                        <svg class="h-10 w-10 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V7a4 4 0 118 0v4" />
                        </svg>
                        <p class="font-semibold text-ink">Bạn đã xem hết phần xem thử</p>
                        <p class="max-w-sm text-sm text-slate-600"
                            x-text="'Bản xem thử có ' + previewPages + ' trên ' + doc?.page_count + ' trang. Mua gói để đọc tiếp các trang còn lại.'"></p>
                        <button type="button" x-on:click="prev()" class="text-sm font-medium text-brand hover:text-brand-dark">
                            ← Quay lại trang <span x-text="previewPages"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Audio --}}
            <div x-show="doc?.type === 'audio'" class="space-y-4 py-4">
                <audio x-ref="audio" :src="audioUrl || null" controls autoplay x-on:ended="audioEnded = true" class="w-full"></audio>
                <p x-show="!audioEnded" class="text-center text-sm text-slate-500"
                    x-text="'Bản nghe thử dài ' + doc?.preview_seconds + ' giây đầu.'"></p>
                <div x-show="audioEnded" class="rounded-xl bg-amber-50 px-4 py-3 text-center text-sm text-amber-800 ring-1 ring-amber-200">
                    Đã hết <span x-text="doc?.preview_seconds"></span> giây nghe thử. Mua gói để nghe đầy đủ bài này.
                </div>
            </div>
        </x-async-state>
    </div>
</x-modal>
