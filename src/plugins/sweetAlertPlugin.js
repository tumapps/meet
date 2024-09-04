// src/plugins/sweetAlertPlugin.js
import { showAlert, showToast } from '@/utilities/sweetAlert';

export default {
    install: (app) => {
        app.config.globalProperties.$showAlert = showAlert;
        app.config.globalProperties.$showToast = showToast;
    }
};
