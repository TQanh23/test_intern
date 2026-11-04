import { errorMessage, getPackage } from './api';
import { describeDocument, formatPrice, levelClass } from './format';

export default (slug) => ({
    pkg: null,
    status: 'loading',
    error: '',
    // id các unit đang mở. Mảng thay vì một id để mở nhiều unit cùng lúc được.
    openUnits: [],

    formatPrice,
    levelClass,
    describeDocument,

    init() {
        this.load();
    },

    async load() {
        this.status = 'loading';

        try {
            this.pkg = await getPackage(slug);
            this.openUnits = this.pkg.units.slice(0, 1).map((unit) => unit.id);
            this.status = 'ready';
        } catch (error) {
            this.error = errorMessage(error);
            this.status = 'error';
        }
    },

    get documentsCount() {
        return this.pkg.units.reduce((total, unit) => total + unit.documents.length, 0);
    },

    isOpen(unitId) {
        return this.openUnits.includes(unitId);
    },

    toggle(unitId) {
        this.openUnits = this.isOpen(unitId)
            ? this.openUnits.filter((id) => id !== unitId)
            : [...this.openUnits, unitId];
    },

    preview(doc) {
        // previewModal nghe event này trên window, nên modal dùng được ở bất kỳ trang nào.
        this.$dispatch('preview-document', doc);
    },
});
