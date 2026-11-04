import axios from 'axios';

// Gọi API cùng domain bằng cookie session (Sanctum statefulApi), không cần token.
// axios tự đọc cookie XSRF-TOKEN và gửi header X-XSRF-TOKEN cho request POST.
const api = axios.create({ baseURL: '/api', headers: { Accept: 'application/json' } });

export const getPackages = (params) => api.get('/packages', { params }).then((response) => response.data);

export const getPackage = (slug) => api.get(`/packages/${encodeURIComponent(slug)}`).then((response) => response.data.data);

export const requestPreview = (documentId) => api.post(`/documents/${documentId}/preview`).then((response) => response.data.data);

export const getPreviewStats = () => api.get('/admin/preview-stats').then((response) => response.data.data);

/**
 * Đổi lỗi kỹ thuật thành câu người dùng hiểu được.
 */
export function errorMessage(error) {
    if (!axios.isAxiosError(error)) {
        return 'Không mở được nội dung. Vui lòng thử lại.';
    }

    if (!error.response) {
        return 'Không kết nối được máy chủ. Kiểm tra mạng rồi thử lại.';
    }

    const messages = {
        401: 'Phiên đăng nhập đã hết. Tải lại trang để đăng nhập lại.',
        403: 'Bạn không có quyền xem nội dung này.',
        404: 'Không tìm thấy nội dung.',
        419: 'Phiên đăng nhập đã hết. Tải lại trang để đăng nhập lại.',
        429: 'Bạn xem thử quá nhiều lần. Đợi 1 phút rồi thử lại.',
    };

    return messages[error.response.status] ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
}
