// src/utils/sweetAlert.js
import Swal from 'sweetalert2';

// Show alert function
export function showAlert({
    title = 'Alert',
    text = '',
    icon = 'warning',
    confirmButtonText = 'OK',
    showCancelButton = true,
    confirmButtonColor = '#d33',
    cancelButtonColor = '#076232',
    ...otherOptions
} = {}) {
    return Swal.fire({
        title,
        text,
        icon,
        confirmButtonText,
        showCancelButton,
        confirmButtonColor,
        cancelButtonColor,
        ...otherOptions // Spread additional options here
    });
}

// Show toast function
export function showToast({ title = '', icon = '#fff', timer = 3000, position = 'top' }, background = '#fff', grow = '') {
    return Swal.fire({
        toast: true,
        title,
        icon,
        position,
        timer,
        background,
        grow,
        showConfirmButton: false, // Disable the confirmation button
        timerProgressBar: true,   // Show a timer progress bar
        customClass: {
            popup: 'full-width-toast',
        },
    });
}

