<template>
  <b-row>
    <b-col md="12">
      <page-header title="Doctor visit">
        <div class="card-action mt-2 mt-sm-0">
          <a class="btn btn-primary" v-b-toggle.sign-form> Close </a>
        </div>
      </page-header>
    </b-col>
    <b-col cols="12">
      <b-collapse id="sign-form" :visible="true">
        <b-card>
          <b-row class="align-items-end justify-content-between">
            <b-col lg="8">
              <div class="form-group">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" aria-describedby="name" />
              </div>
              <div class="form-group">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" aria-label="Default select example" id="department">
                  <option selected>select department</option>
                  <option value="1">Anesthesiologists</option>
                  <option value="2">Cardiologists</option>
                  <option value="3">Dermatologists</option>
                  <option value="4">Gastroenterologists</option>
                  <option value="5">Hematologists</option>
                  <option value="6">Internists</option>
                  <option value="7">Medical Geneticists</option>
                  <option value="8">Nephrologists</option>
                  <option value="9">Neurologists</option>
                  <option value="10">Ophthalmologists</option>
                </select>
              </div>
              <div class="form-group">
                <label for="time" class="form-label">In Time</label>
                <flat-picker id="time" :config="timePicker" className="form-control" v-model="inTime" placeholder="In Time" readonly="readonly"></flat-picker>
              </div>
              <div class="form-group mb-0">
                <label for="out" class="form-label">Out Time</label>
                <flat-picker id="out" :config="timePicker" className="form-control" v-model="outTime" placeholder="Out Time" readonly="readonly"></flat-picker>
              </div>
            </b-col>
            <b-col lg="4" class="mt-4 mt-lg-0">
              <h5 class="text-center mb-4">Signature</h5>
              <div class="d-flex justify-content-center">
                <signature-pad id="visitsignature" class="roundCorners" :modelValue="signatureFile" @input="onInput" :customStyle="customStyle" :saveType="saveType" :saveOutput="saveOutput" ref="signaturePad" />
              </div>
              <div class="text-center mt-4">
                <button class="btn btn-primary" @click="saveSignature">Save</button>
                <button class="btn btn-danger ms-3" @click="clearSignature">Clear</button>
              </div>
            </b-col>
          </b-row>
        </b-card>
      </b-collapse>
    </b-col>
    <b-col lg="12">
      <b-card body-class="px-0">
        <div class="table-responsive">
          <table id="datatable" ref="inputTableRef" class="table dataTable" data-toggle="data-table">
            <tfoot>
              <tr class="filters">
                <th title="Name">Name</th>
                <th title="Department">Department</th>
                <th title="In Time">In Time</th>
                <th title="Out Time">Out Time</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </b-card>
    </b-col>
  </b-row>
</template>

<script>
import { reactive, ref, toRefs } from 'vue'
import SignaturePad from 'vue3-signature-pad'
import FlatPicker from 'vue-flatpickr-component'
import PageHeader from '@/components/custom/header/PageHeader.vue'
//data table
import useDataTable from '../../../hooks/useDatatable.js'

// IMG
import img1 from '@/assets/modules/appointment/images/signature/01.svg'
import img2 from '@/assets/modules/appointment/images/signature/02.svg'
import img3 from '@/assets/modules/appointment/images/signature/04.svg'
import img4 from '@/assets/modules/appointment/images/signature/03.svg'
import img5 from '@/assets/modules/appointment/images/signature/05.svg'

export default {
  components: {
    PageHeader,
    FlatPicker,
    SignaturePad
  },
  setup() {
    const timePicker = ref({
      wrap: true,
      enableTime: true,
      enableSeconds: true,
      noCalendar: true
    })
    const inTime = ref('')
    const outTime = ref('')
    const signatures = ref([
      { name: 'Joan Doe', department: 'Anesthesiologists', inTime: '--', outTime: '--', status: 1, image: require('@/assets/modules/appointment/images/signature/01.svg') },
      { name: 'Marcelo Vieira', department: 'Endocrinologists', inTime: '08:00 AM', outTime: '-', status: 0, image: require('@/assets/modules/appointment/images/signature/02.svg') },
      { name: 'Anne Hathaway', department: 'Gastroenterologists', inTime: '02:00 PM', outTime: '06:00 PM', status: 0, image: require('@/assets/modules/appointment/images/signature/03.svg') },
      { name: 'Ella Mai', department: '', inTime: '04:00 PM', outTime: '08:00 PM', status: 1, image: require('@/assets/modules/appointment/images/signature/04.svg') },
      { name: 'Willow Smith', department: 'Internists', inTime: '08:00 AM', outTime: '12:00 PM', status: 1, image: require('@/assets/modules/appointment/images/signature/05.svg') }
    ])
    const state = reactive({
      customStyle: {
        backgroundColor: '#e9ecef',
        width: '58%',
        position: 'relative',
        marginleft: 'auto',
        marginright: 'auto',
        padding: '0'
      },
      saveType: 'image/svg+xml',
      saveOutput: 'file'
    })
    const signatureDataURL = ref(null)
    const signatureFile = ref(null)
    const signaturePad = ref(null)

    const getSignaturePad = () => {
      if (!signaturePad.value) {
        throw new Error('No signature pad ref could be found')
      }
      return signaturePad.value
    }

    const clearSignature = () => {
      const signature = getSignaturePad().clearSignature()
      return signature
    }

    const saveSignature = () => {
      const signature = getSignaturePad().saveSignature()
      return signature
    }

    const onInput = (value) => {
      console.log('calling on input', value)
      if (!value) {
        signatureDataURL.value = null
        signatureFile.value = null
      } else if (value instanceof File) {
        signatureDataURL.value = null
        signatureFile.value = value
      } else {
        signatureDataURL.value = value
        signatureFile.value = null
      }
    }

    // data table start
    const tableData = ref([
      {
        name: 'Anne Hathaway',
        department: 'Gastroenterologists',
        intime: '02:00 PM',
        outtime: '06:00 PM',
        actions: {
          color: 'danger',
          status: 'Doctor Out'
        },
        signature: img1
      },
      {
        name: 'Ella Mai',
        department: 'Hematologists',
        intime: '04:00 PM',
        outtime: '08:00 PM',
        actions: {
          color: 'success',
          status: 'Doctor In'
        },
        signature: img2
      },
      {
        name: 'Jhon Doe',
        department: 'Anesthesiologists',
        intime: '--',
        outtime: '--',
        actions: {
          color: 'danger',
          status: 'Doctor Out'
        },
        signature: img3
      },
      {
        name: 'Marcelo Vieira',
        department: 'Endocrinologists',
        intime: '08:00 AM',
        outtime: '--',
        actions: {
          color: 'success',
          status: 'Doctor In'
        },
        signature: img4
      },
      {
        name: 'Willow Smith',
        department: 'Internists',
        intime: '08:00 AM',
        outtime: '12:00 PM',
        actions: {
          color: 'success',
          status: 'Doctor In'
        },
        signature: img5
      }
    ])

    const columns = ref([
      {
        title: 'Name',
        data: 'name',
        render: function (row) {
          return `<h6>${row}</h6>`
        }
      },
      {
        title: 'Department',
        data: 'department'
      },
      {
        title: 'In Time',
        data: 'intime'
      },
      {
        title: 'Out Time',
        data: 'outtime'
      },
      {
        title: 'Status',
        data: 'actions',
        render: function (data) {
          return '<div class="bg-' + data.color + ' text-white badge rounded-pill">' + data.status + '</div>'
        }
      },
      {
        title: 'Signature',
        data: 'signature',
        render: function (data) {
          return '<img class="avatar-50 sing-img" src=' + data + ' alt="sign" />'
        }
      }
    ])
    const inputTableRef = ref(null)
    useDataTable({
      tableRef: inputTableRef,
      columns: columns.value,
      data: tableData.value,
      isFilterColumn: true
    })
    // data table end

    return {
      // state
      ...toRefs(state),
      signaturePad,
      signatureDataURL,
      signatureFile,
      timePicker,
      inTime,
      outTime,
      signatures,
      // methods
      clearSignature,
      saveSignature,
      onInput,
      inputTableRef
    }
  }
}
</script>

<style></style>
