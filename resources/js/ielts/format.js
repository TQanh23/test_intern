// Hàm hiển thị dùng chung cho trang danh sách, chi tiết và modal.

const priceFormat = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });

export const formatPrice = (vnd) => priceFormat.format(vnd);

// Màu chip level theo Figma. Viết đủ tên class để Tailwind quét được (tailwind.config.js có quét resources/js).
const LEVEL_CLASSES = {
    1: 'bg-brand text-white',
    2: 'bg-brand-light text-ink',
    3: 'bg-ink text-white',
};

export const levelClass = (level) => LEVEL_CLASSES[level] ?? 'bg-surface-low text-slate-600';

export function describeDocument(doc) {
    return doc.type === 'pdf'
        ? `PDF · ${doc.page_count} trang · xem thử ${doc.preview_pages} trang đầu`
        : `Audio · nghe thử ${doc.preview_seconds} giây đầu`;
}
