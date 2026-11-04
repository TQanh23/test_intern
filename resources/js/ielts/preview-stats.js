import { errorMessage, getPreviewStats } from './api';

export default () => ({
    stats: null,
    status: 'loading',
    error: '',

    init() {
        this.load();
    },

    async load() {
        this.status = 'loading';

        try {
            this.stats = await getPreviewStats();
            this.status = this.stats.packages.length ? 'ready' : 'empty';
        } catch (error) {
            this.error = errorMessage(error);
            this.status = 'error';
        }
    },

    // Độ dài thanh so với dòng nhiều lượt nhất. API đã sắp giảm dần nên dòng đầu là lớn nhất.
    barWidth(rows, row) {
        return `${Math.round((row.views / rows[0].views) * 100)}%`;
    },
});
