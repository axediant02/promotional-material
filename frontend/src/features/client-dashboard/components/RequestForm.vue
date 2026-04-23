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
    <div v-if="!file">
      <label class="mb-2 block text-sm font-medium text-ink dark:text-white">Request Type</label>
      <div class="space-y-2">
        <label
          v-for="type in requestTypes"
          :key="type.value"
          :class="[
            'flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 transition-all',
            form.request_type === type.value
              ? 'border-brand-500 bg-brand-50 dark:border-white/20 dark:bg-white/10'
              : 'border-border hover:border-brand-300 dark:border-white/10 dark:hover:border-white/20'
          ]"
        >
          <input
            type="radio"
            :value="type.value"
            v-model="form.request_type"
            class="h-4 w-4 accent-[rgb(95,80,155)]"
          />
          <div class="flex-1">
            <p class="font-medium text-ink dark:text-white">{{ type.label }}</p>
            <p class="text-xs text-muted dark:text-zinc-300">{{ type.description }}</p>
          </div>
        </label>
      </div>
    </div>

    <div
      v-else
      class="rounded-xl border border-brand-200 bg-brand-50 p-3 dark:border-white/10 dark:bg-white/10"
    >
      <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-100">Request Type</p>
      <p class="mt-2 font-medium text-ink dark:text-white">Update Asset</p>
      <p class="mt-1 text-xs text-muted dark:text-zinc-300">This request is tied to the selected file and will be submitted as an update request.</p>
    </div>

    <!-- Title -->
    <div>
      <label for="title" class="mb-2 block text-sm font-medium text-ink dark:text-white">
        Title <span class="text-red-500">*</span>
      </label>
      <input
        id="title"
        v-model="form.title"
        type="text"
        placeholder="Brief title for your request"
        class="pm-input w-full rounded-xl px-4 py-3 text-sm"
      />
      <p class="mt-2 text-xs text-muted dark:text-zinc-400">Use a short, specific title so your request is easy to scan later.</p>
    </div>

    <div v-if="file" class="rounded-xl bg-white/70 p-3 ring-1 ring-border/70 dark:bg-white/5 dark:ring-white/10">
      <p class="text-xs text-muted dark:text-zinc-400">Selected file</p>
      <p class="truncate font-medium text-ink dark:text-white">{{ file.file_name }}</p>
    </div>

    <div
      v-else-if="!folder?.folder_id"
      class="rounded-xl border border-brand-200 bg-brand-50 p-3 dark:border-white/10 dark:bg-white/10"
    >
      <p class="text-sm text-brand-700 dark:text-white">
        Your client folder will be created automatically when you submit this first request.
      </p>
    </div>

    <!-- Description -->
    <div>
      <label for="description" class="mb-2 block text-sm font-medium text-ink dark:text-white">
        Changes Needed <span class="text-red-500">*</span>
      </label>
      <textarea
        id="description"
        v-model="form.description"
        rows="4"
        placeholder="Describe what changes or updates you need for this file..."
        class="pm-input w-full resize-none rounded-xl px-4 py-3 text-sm"
      ></textarea>
      <p class="mt-2 text-xs text-muted dark:text-zinc-400">Helpful details: exact text changes, target size or format, deadline context, and what should stay unchanged.</p>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="rounded-xl bg-red-50 p-3 dark:bg-red-500/10">
      <p class="text-sm text-red-600 dark:text-red-200">{{ error }}</p>
    </div>

    <!-- Actions -->
    <div class="flex gap-3 pt-2">
      <button
        type="button"
        @click="resetForm(); emit('close')"
        class="flex-1 rounded-xl border border-border px-4 py-3 text-sm font-medium text-muted transition hover:border-brand-300 hover:bg-brand-50 dark:border-white/10 dark:text-white dark:hover:border-white/20 dark:hover:bg-white/10"
      >
        Clear
      </button>
      <button
        type="submit"
        :disabled="isSubmitting"
        :class="[
          'pm-gradient-primary flex-1 rounded-xl px-4 py-3 text-sm font-medium transition',
          isSubmitting
            ? 'cursor-not-allowed opacity-50'
            : 'hover:brightness-110'
        ]"
      >
        {{ isSubmitting ? 'Submitting...' : 'Submit Request' }}
      </button>
    </div>
  </form>
</template>
