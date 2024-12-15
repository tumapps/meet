<template>
  <div class="border-bottom pb-3 d-flex align-items-center justify-content-between">
    <h5>{{ $t('file-manager.sidebar.document') }}</h5>
    <b-button variant="primary" v-b-modal.exampleModal>
      <div class="d-flex justify-content-center">
        <svg width="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path opacity="0.4" d="M16.191 2H7.81C4.77 2 3 3.78 3 6.83V17.16C3 20.26 4.77 22 7.81 22H16.191C19.28 22 21 20.26 21 17.16V6.83C21 3.78 19.28 2 16.191 2Z" fill="currentColor"></path>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M8.07996 6.6499V6.6599C7.64896 6.6599 7.29996 7.0099 7.29996 7.4399C7.29996 7.8699 7.64896 8.2199 8.07996 8.2199H11.069C11.5 8.2199 11.85 7.8699 11.85 7.4289C11.85 6.9999 11.5 6.6499 11.069 6.6499H8.07996ZM15.92 12.7399H8.07996C7.64896 12.7399 7.29996 12.3899 7.29996 11.9599C7.29996 11.5299 7.64896 11.1789 8.07996 11.1789H15.92C16.35 11.1789 16.7 11.5299 16.7 11.9599C16.7 12.3899 16.35 12.7399 15.92 12.7399ZM15.92 17.3099H8.07996C7.77996 17.3499 7.48996 17.1999 7.32996 16.9499C7.16996 16.6899 7.16996 16.3599 7.32996 16.1099C7.48996 15.8499 7.77996 15.7099 8.07996 15.7399H15.92C16.319 15.7799 16.62 16.1199 16.62 16.5299C16.62 16.9289 16.319 17.2699 15.92 17.3099Z" fill="currentColor"></path>
        </svg>
        <p class="ms-3 mb-0">Add Document</p>
      </div>
    </b-button>
  </div>
  <h4 class="py-3 mb-0">{{ $t('file-manager.sidebar.recently-viewed') }}</h4>
  <b-row class="row-cols-1 row-cols-md-2 row-cols-lg-4">
    <div class="col" v-for="(data, index) in datas" :key="index">
      <document-widget :imageDate="data.imageDate" :imgName="data.imgName" :imgLink="data.imgLink" :imgChange="data.imgChange" :imgMb="data.imgMb" :docType="data.docType" :docName="data.docName" @click="docview(data.value)" />
    </div>
  </b-row>
  <h3 class="my-3">{{ $t('file-manager.sidebar.all-documents') }}</h3>
  <b-row class="row-cols-1 row-cols-md-2 row-cols-lg-4">
    <div class="col" v-for="(data, index) in items" :key="index">
      <document-widget :imageDate="data.imageDate" :imgName="data.imgName" :imgLink="data.imgLink" :imgChange="data.imgChange" :imgMb="data.imgMb" :docType="data.docType" :docName="data.docName" @click="docview(data.value)" />
    </div>
  </b-row>
  <!-- Modal -->
  <b-modal id="exampleModal" size="lg">
    <b-row>
      <b-col lg="12">
        <b-card no-body>
          <b-card no-body class="text-center">
            <uppy-dashboard id="drag-drop-area" />
          </b-card>
        </b-card>
      </b-col>
    </b-row>
  </b-modal>
</template>
<script>
import DocumentWidget from '@/components/modules/file-manager/widgets/DocumentWidget.vue'
import UppyDashboard from '@/components/custom/uppy/UppyDashboard.vue'
// import Dashboard from '@/components/custom/pro/uppy/uppy'
// import '@/assets/modules/file-manager/js/doc-viewer.js'
export default {
  name: 'DocumentFolder',
  components: {
    DocumentWidget,
    UppyDashboard
  },
  data() {
    return {
      url: '',
      datas: [
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Doc-123472578.pdf',
          imgLink: 'just now',
          imgChange: require('@/assets/modules/file-manager/images/pdf.svg'),
          imgMb: '5mb',
          docType: 'PDF',
          docName: 'demo.pdf',
          value: 'https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pdf'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Doc-25783.xlsx',
          imgLink: '2 days ago',
          imgChange: require('@/assets/modules/file-manager/images/excel.svg'),
          imgMb: '2mb',
          docType: 'XLSX',
          docName: 'demo.xlsx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.xlsx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'abc-25783.doc',
          imgLink: 'a month ago',
          imgChange: require('@/assets/modules/file-manager/images/word.svg'),
          imgMb: '7mb',
          docType: 'DOCX',
          docName: 'demo.docx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.docx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'xyz-25783.pptx',
          imgLink: '2 days ago',
          imgChange: require('@/assets/modules/file-manager/images/ppt.svg'),
          imgMb: '12mb',
          docType: 'PPTX',
          docName: 'demo.pptx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pptx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'abd-25783.doc',
          imgLink: 'a month ago',
          imgChange: require('@/assets/modules/file-manager/images/word.svg'),
          imgMb: '10mb',
          docType: 'DOCX',
          docName: 'demo.docx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.docx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'abc-123472578.pptx',
          imgLink: 'just now',
          imgChange: require('@/assets/modules/file-manager/images/ppt.svg'),
          imgMb: '2mb',
          docType: 'PPTX',
          docName: 'demo.pptx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pptx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'list-25783.xlsx',
          imgLink: '2 days ago',
          imgChange: require('@/assets/modules/file-manager/images/excel.svg'),
          imgMb: '2mb',
          docType: 'XLSX',
          docName: 'demo.xlsx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.xlsx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Themes-123472578.pdf',
          imgLink: 'just now',
          imgChange: require('@/assets/modules/file-manager/images/pdf.svg'),
          imgMb: '15mb',
          docType: 'PDF',
          docName: 'demo.pdf',
          value: 'https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pdf'
        }
      ],
      items: [
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Students-25783.xlsx',
          imgLink: 'just now',
          imgChange: require('@/assets/modules/file-manager/images/excel.svg'),
          imgMb: '12mb',
          docType: 'XLSX',
          docName: 'demo.xlsx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.xlsx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Projects-25783.doc',
          imgLink: '2 days ago',
          imgChange: require('@/assets/modules/file-manager/images/word.svg'),
          imgMb: '14mb',
          docType: 'DOCX',
          docName: 'demo.docx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.docx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Certifacte-25783.pdf',
          imgLink: 'a month ago',
          imgChange: require('@/assets/modules/file-manager/images/pdf.svg'),
          imgMb: '24mb',
          docType: 'PDF',
          docName: 'demo.pdf',
          value: 'https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pdf'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'CV-25783.pptx',
          imgLink: '2 days ago',
          imgChange: require('@/assets/modules/file-manager/images/ppt.svg'),
          imgMb: '12mb',
          docType: 'PPTX',
          docName: 'demo.pptx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pptx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Portfolio-25783.doc',
          imgLink: 'a month ago',
          imgChange: require('@/assets/modules/file-manager/images/word.svg'),
          imgMb: '18mb',
          docType: 'DOCX',
          docName: 'demo.docx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.docx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'Posts list-25783.xlsx',
          imgLink: 'just now',
          imgChange: require('@/assets/modules/file-manager/images/excel.svg'),
          imgMb: '5mb',
          docType: 'XLSX',
          docName: 'demo.xlsx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.xlsx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'abc-25783.pptx',
          imgLink: '2 days ago',
          imgChange: require('@/assets/modules/file-manager/images/ppt.svg'),
          imgMb: '3mb',
          docType: 'PPTX',
          docName: 'demo.pptx',
          value: 'https://view.officeapps.live.com/op/view.aspx?src=https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pptx'
        },
        {
          imageDate: 'Dec 13, 2020',
          imgName: 'doc-25783.pdf',
          imgLink: 'just now',
          imgChange: require('@/assets/modules/file-manager/images/pdf.svg'),
          imgMb: '8mb',
          docType: 'PDF',
          docName: 'demo.pdf',
          value: 'https://templates.iqonic.design/hope-ui/pro/html/file-manager/assets/images/doc-files/demo.pdf'
        }
      ]
    }
  },
  methods: {
    docview(val) {
      this.url = val
    }
  }
}
</script>
