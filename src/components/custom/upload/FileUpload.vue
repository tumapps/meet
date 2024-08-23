<template>
    <form @submit.prevent="uploadFile">
        <input type="file" ref="fileInput" @change="handleFileChange" />
        <button type="submit">Upload File</button>
        <p v-if="uploadStatus">{{ uploadStatus }}</p>
    </form>
</template>

<script>
export default{
    components:{
        name: FileUpload,
    },
};

import { ref, defineEmits } from 'vue';

const fileInputRef = ref<HTMLInputElement | null>(null);
const uploadStatus = ref<string>('');

// Define emits to declare events that can be emitted by the component
const emits = defineEmits(['fileUploaded']);

const uploadFile = async () => {
    if (!fileInputRef.value || !fileInputRef.value.files[0]) {
        uploadStatus.value = 'Please select a file.';
        return;
    }

    const formData = new FormData();
    formData.append('file', fileInputRef.value.files[0]);

    try {
        const response = await fetch('http://your-api-endpoint/upload', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Failed to upload file');
        }

        const responseData = await response.json();
        console.log('File uploaded:', responseData);
        uploadStatus.value = 'File uploaded successfully!';

        // Emit the 'fileUploaded' event with a success message
        emits.fileUploaded('File uploaded successfully!');
    } catch (error) {
        console.error('Error uploading file:', error);
        uploadStatus.value = 'Error uploading file.';
        // Optionally emit an error event if needed
    }
};

const handleFileChange = () => {
    uploadStatus.value = ''; // Clear upload status when a new file is selected
};
</script>