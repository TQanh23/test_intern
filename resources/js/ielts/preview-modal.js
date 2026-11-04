import { errorMessage, requestPreview } from './api';
import { describeDocument } from './format';

// 25% để trang A4 vẫn vừa bề ngang màn hình điện thoại 320 px.
const MIN_SCALE = 0.25;
const MAX_SCALE = 3;
const SCALE_STEP = 0.25;

let pdfjsPromise = null;

/**
 * Nạp pdf.js lúc mở PDF lần đầu, không nằm trong bundle chính. Các lần sau dùng lại promise cũ.
 */
function loadPdfjs() {
    pdfjsPromise ??= Promise.all([
        import('pdfjs-dist'),
        import('pdfjs-dist/build/pdf.worker.min.mjs?url'),
    ]).then(([pdfjs, worker]) => {
        pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
        return pdfjs;
    });

    return pdfjsPromise;
}

const clamp = (value) => Math.min(MAX_SCALE, Math.max(MIN_SCALE, value));

/**
 * Đặt bên trong <x-modal>, nên đọc và ghi được biến `show` của modal.
 */
export default () => {
    // Giữ ngoài state của Alpine: Alpine bọc state bằng Proxy, làm hỏng private field của object pdf.js.
    let pdf = null;
    let renderTask = null;
    // Mỗi lần mở hoặc đóng thì tăng. Kết quả của lần mở cũ (đã đóng, hoặc đã mở tài liệu khác) bị bỏ qua.
    let openId = 0;

    return {
        doc: null,
        status: 'idle',
        error: '',
        audioUrl: '',
        page: 1,
        previewPages: 0,
        scale: 1,
        limitReached: false,
        audioEnded: false,

        describeDocument,
        minScale: MIN_SCALE,
        maxScale: MAX_SCALE,

        init() {
            // Modal đóng bằng Esc, bấm nền hoặc nút X đều đổi `show` về false.
            this.$watch('show', (value) => value || this.reset());
        },

        async open(doc) {
            this.reset();
            const currentId = openId;
            this.doc = doc;
            this.status = 'loading';
            this.$dispatch('open-modal', 'document-preview');

            try {
                const { url } = await requestPreview(doc.id);
                if (currentId !== openId) return;

                if (doc.type === 'audio') {
                    this.audioUrl = url;
                    this.status = 'ready';
                    return;
                }

                const pdfjs = await loadPdfjs();
                const loaded = await pdfjs.getDocument(url).promise;
                if (currentId !== openId) {
                    loaded.destroy();
                    return;
                }

                pdf = loaded;
                this.previewPages = pdf.numPages;
                this.status = 'ready';
                // Đợi khung xem hiện ra rồi mới đo bề rộng để khớp trang. x-show của Alpine hiện phần tử trong
                // một setTimeout, nên $nextTick vẫn còn sớm, phải đợi thêm một setTimeout nữa.
                await this.$nextTick();
                await new Promise((resolve) => setTimeout(resolve));
                if (currentId !== openId) return;
                await this.fitWidth();
            } catch (error) {
                if (currentId !== openId) return;
                this.error = errorMessage(error);
                this.status = 'error';
            }
        },

        retry() {
            this.open(this.doc);
        },

        reset() {
            openId++;
            renderTask?.cancel();
            pdf?.destroy();
            pdf = null;
            this.$refs.audio?.pause();
            this.audioUrl = '';
            this.status = 'idle';
            this.error = '';
            this.page = 1;
            this.previewPages = 0;
            this.limitReached = false;
            this.audioEnded = false;
        },

        async fitWidth() {
            const page = await pdf.getPage(this.page);
            const width = page.getViewport({ scale: 1 }).width;
            // Trừ 32px padding của khung xem. Khung chưa hiện (bề rộng 0) thì giữ 100%.
            const available = this.$refs.viewer.clientWidth - 32;
            this.scale = clamp(available > 0 ? available / width : 1);
            await this.render();
        },

        async render() {
            const page = await pdf.getPage(this.page);
            const viewport = page.getViewport({ scale: this.scale });
            const canvas = this.$refs.canvas;
            // Vẽ theo devicePixelRatio để chữ không bị mờ trên màn hình độ phân giải cao.
            const ratio = window.devicePixelRatio || 1;

            canvas.width = Math.floor(viewport.width * ratio);
            canvas.height = Math.floor(viewport.height * ratio);
            canvas.style.width = `${Math.floor(viewport.width)}px`;
            canvas.style.height = `${Math.floor(viewport.height)}px`;

            // Bấm chuyển trang hoặc zoom liên tục: huỷ lần vẽ trước để hai lần vẽ không chồng lên nhau.
            renderTask?.cancel();
            renderTask = page.render({
                canvasContext: canvas.getContext('2d'),
                viewport,
                transform: ratio === 1 ? undefined : [ratio, 0, 0, ratio, 0, 0],
            });

            try {
                await renderTask.promise;
            } catch (error) {
                if (error?.name !== 'RenderingCancelledException') throw error;
            }
        },

        next() {
            if (this.page < this.previewPages) {
                this.page++;
                this.render();
            } else if (this.doc.page_count > this.previewPages) {
                // Bản xem thử chỉ có previewPages trang, trang sau đó phải mua gói mới đọc được.
                this.limitReached = true;
            }
        },

        prev() {
            if (this.limitReached) {
                this.limitReached = false;
            } else if (this.page > 1) {
                this.page--;
                this.render();
            }
        },

        zoom(step) {
            this.scale = clamp(this.scale + step * SCALE_STEP);
            this.render();
        },

        get zoomLabel() {
            return `${Math.round(this.scale * 100)}%`;
        },
    };
};
