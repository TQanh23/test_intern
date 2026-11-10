import { errorMessage, getPackages } from './api';
import { formatPrice, levelClass } from './format';

export default () => ({
    levels: [
        { value: '', label: 'Tất cả level' },
        { value: 1, label: 'Level 1' },
        { value: 2, label: 'Level 2' },
        { value: 3, label: 'Level 3' },
    ],
    level: '',
    q: '',
    page: 1,
    packages: [],
    meta: null,
    status: 'loading',
    error: '',
    requestId: 0,

    formatPrice,
    levelClass,

    init() {
        this.load();
        // Ô tìm kiếm dùng x-model.debounce nên chỉ gọi API khi người dùng ngừng gõ 300 ms.
        this.$watch('level', () => this.goTo(1));
        this.$watch('q', () => this.goTo(1));
    },

    goTo(page) {
        this.page = page;
        this.load();
    },

    async load() {
        // Đổi bộ lọc nhanh có thể làm request cũ về sau request mới. Chỉ nhận kết quả của request cuối cùng.
        const requestId = ++this.requestId;
        this.status = 'loading';

        try {
            const body = await getPackages({ level: this.level || undefined, q: this.q || undefined, page: this.page });

            if (requestId !== this.requestId) return;
            this.packages = body.data;
            this.meta = body.meta;
            this.status = body.data.length ? 'ready' : 'empty';
        } catch (error) {
            if (requestId !== this.requestId) return;
            this.error = errorMessage(error);
            this.status = 'error';
        }
    },

    clearFilters() {
        this.level = '';
        this.q = '';
    },
});
