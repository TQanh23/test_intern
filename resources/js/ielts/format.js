// Hàm hiển thị dùng chung cho trang danh sách, chi tiết và modal.

const priceFormat = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });

export const formatPrice = (vnd) => priceFormat.format(vnd);

// Viết đủ tên class để Tailwind quét được (tailwind.config.js có quét resources/js).
const LEVEL_CLASSES = {
    1: 'bg-emerald-100 text-emerald-700',
    2: 'bg-sky-100 text-sky-700',
    3: 'bg-violet-100 text-violet-700',
};

export const levelClass = (level) => LEVEL_CLASSES[level] ?? 'bg-slate-100 text-slate-700';

export function describeDocument(doc) {
    return doc.type === 'pdf'
        ? `PDF · ${doc.page_count} trang · xem thử ${doc.preview_pages} trang đầu`
        : `Audio · nghe thử ${doc.preview_seconds} giây đầu`;
}
