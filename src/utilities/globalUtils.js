// src/utils/globalUtils.js

export const convertToDate = (unixTimestamp) => {
    const date = new Date(unixTimestamp * 1000);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
};

// Add more utility functions here if needed

export default {
    convertToDate
};
