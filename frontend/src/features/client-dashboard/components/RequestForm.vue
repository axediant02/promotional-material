<script setup>
import { ref } from 'vue'
import { createRequest } from '../../../services/requestService'

const props = defineProps({
  file: { type: Object, default: null },
  folder: { type: Object, default: null },
})

const emit = defineEmits(['close', 'success'])

const isSubmitting = ref(false)
const error = ref(null)
const form = ref({
  title: '',
  description: '',
  request_type: 'update_asset',
})

const requestTypes = [
  { value: 'update_asset', label: 'Update Asset', description: 'Request modifications to existing file' },
  { value: 'new_asset', label: 'New Asset', description: 'Request a completely new file' },
]

const resetForm = () => {
  error.value = null
  form.value = {
    title: '',
    description: '',
    request_type: props.file ? 'update_asset' : 'new_asset',
  }
}

const validateForm = () => {
  if (!form.value.title.trim()) {
    error.value = 'Please enter a title for your request.'
    return false
  }
  if (!form.value.description.trim()) {
    error.value = 'Please describe what changes you need.'
    return false
  }
  return true
}

const submitRequest = async () => {
  error.value = null
  
  if (!validateForm()) {
    return
  }

  isSubmitting.value = true

  try {
    const payload = {
      title: form.value.title.trim(),
      description: form.value.description.trim(),
      request_type: form.value.request_type,
    }

    const response = await createRequest(payload)

    resetForm()

    emit('success', response.data)
  } catch (err) {
    console.error('Request submission failed:', err)
    error.value =
      err.response?.data?.errors?.due_date?.[0]
      || err.response?.data?.errors?.folder_id?.[0]
      || err.response?.data?.message
      || 'Failed to submit request. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <form @submit.prevent="submitRequest" class="space-y-4">
    <!-- Request Type -->
    <div>
      <label class="mb-2 block text-sm font-medium text-slate-700">Request Type</label>
      <div class="space-y-2">
        <label
          v-for="type in requestTypes"
          :key="type.value"
          :class="[
            'flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 transition-all',
            form.request_type === type.value
              ? 'border-orange-500 bg-orange-50'
              : 'border-slate-200 hover:border-slate-300'
          ]"
        >
          <input
            type="radio"
            :value="type.value"
            v-model="form.request_type"
            class="h-4 w-4 accent-orange-600"
          />
          <div class="flex-1">
            <p class="font-medium text-slate-900">{{ type.label }}</p>
            <p class="text-xs text-slate-500">{{ type.description }}</p>
          </div>
        </label>
      </div>
    </div>

    <!-- Title -->
    <div>
      <label for="title" class="mb-2 block text-sm font-medium text-slate-700">
        Title <span class="text-red-500">*</span>
      </label>
      <input
        id="title"
        v-model="form.title"
        type="text"
        placeholder="Brief title for your request"
        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
      />
    </div>

    <div v-if="file" class="rounded-xl bg-slate-50 p-3">
      <p class="text-xs text-slate-500">Selected file</p>
      <p class="truncate font-medium text-slate-900">{{ file.file_name }}</p>
    </div>

    <div
      v-else-if="!folder?.folder_id"
      class="rounded-xl border border-amber-200 bg-amber-50 p-3"
    >
      <p class="text-sm text-amber-800">
        Your client folder will be created automatically when you submit this first request.
      </p>
    </div>

    <!-- Description -->
    <div>
      <label for="description" class="mb-2 block text-sm font-medium text-slate-700">
        Changes Needed <span class="text-red-500">*</span>
      </label>
      <textarea
        id="description"
        v-model="form.description"
        rows="4"
        placeholder="Describe what changes or updates you need for this file..."
        class="w-full resize-none rounded-xl border border-slate-300 px-4 py-3 text-sm transition focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
      ></textarea>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="rounded-xl bg-red-50 p-3">
      <p class="text-sm text-red-600">{{ error }}</p>
    </div>

    <!-- Actions -->
    <div class="flex gap-3 pt-2">
      <button
        type="button"
        @click="resetForm(); emit('close')"
        class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
      >
        Clear
      </button>
      <button
        type="submit"
        :disabled="isSubmitting"
        :class="[
          'flex-1 rounded-xl bg-orange-600 px-4 py-3 text-sm font-medium text-white transition',
          isSubmitting
            ? 'cursor-not-allowed opacity-50'
            : 'hover:bg-orange-700'
        ]"
      >
        {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
      </button>
    </div>
  </form>
</template>
