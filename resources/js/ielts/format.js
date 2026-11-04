// Hàm hiển thị dùng chung cho trang danh sách, chi tiết và modal.

const priceFormat = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' });

export const formatPrice = (vnd) => priceFormat.format(vnd);

// Viết đủ tên class để Tailwind quét được (tailwind.config.js có quét resources/js).
const LEVEL_CLASSES = {
    1: 'bg-teal-100 text-teal-800',
    2: 'bg-indigo-100 text-indigo-800',
    3: 'bg-rose-100 text-rose-800',
};

export const levelClass = (level) => LEVEL_CLASSES[level] ?? 'bg-gray-100 text-gray-800';

export function describeDocument(doc) {
    return doc.type === 'pdf'
        ? `PDF · ${doc.page_count} trang · xem thử ${doc.preview_pages} trang đầu`
        : `Audio · nghe thử ${doc.preview_seconds} giây đầu`;
}
