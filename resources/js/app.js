import './bootstrap';

import Alpine from 'alpinejs';
import packageDetail from './ielts/package-detail';
import packageList from './ielts/package-list';
import previewModal from './ielts/preview-modal';
import previewStats from './ielts/preview-stats';

window.Alpine = Alpine;

// Component của trang gói IELTS. Phải đăng ký trước Alpine.start().
Alpine.data('packageList', packageList);
Alpine.data('packageDetail', packageDetail);
Alpine.data('previewModal', previewModal);
Alpine.data('previewStats', previewStats);

Alpine.start();
