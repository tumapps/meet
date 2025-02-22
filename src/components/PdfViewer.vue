<template>
  <div class="pdf-container">
    <canvas ref="pdfCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, defineProps, watch } from 'vue'
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf'

pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdfjs-dist/legacy/build/pdf.worker.js'
// Set the worker source

const props = defineProps({
  pdfUrl: {
    type: String,
    required: true
  }
})

watch(
  () => props.pdfUrl,
  (newUrl) => {
    renderPdf(newUrl)
  }
)

//assing the props to the pdfUrl

// PDF.js worker setup

// Reactive references
const pdfCanvas = ref(null)
const pdfUrl = ref('') // Replace with your PDF URL

const renderPdf = async (url) => {
  try {
    const pdfDoc = await pdfjsLib.getDocument(url).promise

    const canvas = pdfCanvas.value
    const canvasContext = canvas.getContext('2d')

    // Get the first page of the PDF
    const page = await pdfDoc.getPage(1)

    // Set canvas dimensions and render page
    const viewport = page.getViewport({ scale: 1.5 })
    canvas.width = viewport.width
    canvas.height = viewport.height

    const renderContext = {
      canvasContext,
      viewport
    }

    await page.render(renderContext).promise
  } catch (error) {
    console.error('Error rendering PDF:', error)
  }
}

onMounted(() => {
  pdfUrl.value = props.pdfUrl
  renderPdf(pdfUrl.value)
})
</script>

<style>
.pdf-container {
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: auto;
  width: 100%;
  height: 100vh;
  background-color: #f5f5f5;
}

canvas {
  border: 1px solid #ccc;
}
</style>
