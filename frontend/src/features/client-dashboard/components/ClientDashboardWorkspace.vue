<script setup>
import ClientAssetCatalog from './ClientAssetCatalog.vue'
import ClientDashboardTopbar from './ClientDashboardTopbar.vue'
import ClientDeliveryHero from './ClientDeliveryHero.vue'
import ClientRequestHistoryPanel from './ClientRequestHistoryPanel.vue'
import ClientRequestSidebar from './ClientRequestSidebar.vue'
import AssignmentChatWidget from '../../chat/components/AssignmentChatWidget.vue'

const props = defineProps({
  user: { type: Object, default: null },
  notifications: { type: Array, default: () => [] },
  notificationsLoading: { type: Boolean, default: false },
  unreadCount: { type: Number, default: 0 },
  markReadAction: { type: Function, required: true },
  markAllReadAction: { type: Function, required: true },
  folderLabel: { type: String, default: 'Assigned Folder' },
  folder: { type: Object, default: null },
  files: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  requests: { type: Array, default: () => [] },
  requestsLoading: { type: Boolean, default: false },
  searchQuery: { type: String, default: '' },
  viewMode: { type: String, default: 'grid' },
  selectedFileId: { type: String, default: null },
  selectedFile: { type: Object, default: null },
  heroContent: { type: Object, default: () => ({}) },
  catalogSummary: { type: Object, default: () => ({}) },
  supportSummary: { type: Object, default: () => ({}) },
  assignedFolder: { type: Object, default: null },
  requestDrawerOpen: { type: Boolean, default: false },
  requestMode: { type: String, default: 'new_asset' },
  currentUserId: { type: String, default: '' },
})

const emit = defineEmits([
  'update:searchQuery',
  'update:viewMode',
  'open-request',
  'request-change',
  'clear-search',
  'select-file',
  'clear-selected-file',
  'request-created',
  'close-request',
  'update:requestMode',
])

</script>

<template>
  <div class="pm-page min-h-screen bg-transparent text-ink dark:text-white">
    <ClientDashboardTopbar
      :search-query="searchQuery"
      :folder-label="folderLabel"
      :user="user"
      :notifications="notifications"
      :notifications-loading="notificationsLoading"
      :unread-count="unreadCount"
      :mark-read-action="markReadAction"
      :mark-all-read-action="markAllReadAction"
      @update:searchQuery="emit('update:searchQuery', $event)"
      @open-request="emit('open-request')"
    />

    <main class="mx-auto flex w-full max-w-[1680px] flex-col gap-8 px-4 pb-12 pt-6 sm:px-6 lg:px-8">
      <ClientDeliveryHero
        :folder="assignedFolder"
        :user="user"
        :eyebrow="heroContent.eyebrow"
        :title="heroContent.title"
        :accent="heroContent.accent"
        :subtitle="heroContent.subtitle"
        :action-label="heroContent.actionLabel"
        :action-target="heroContent.actionTarget"
        @action-click="emit('open-request')"
      />

      <ClientAssetCatalog
        :files="files"
        :loading="loading"
        :search-query="searchQuery"
        :view-mode="viewMode"
        :folder-label="catalogSummary.folderLabel"
        :last-updated-label="catalogSummary.lastUpdatedLabel"
        :selected-file-id="selectedFileId"
        @update:view-mode="emit('update:viewMode', $event)"
        @request-change="emit('request-change', $event)"
        @clear-search="emit('clear-search')"
        @open-request="emit('open-request')"
      />

      <ClientRequestHistoryPanel
        :requests="requests"
        :loading="requestsLoading"
      />
    </main>

    <ClientRequestSidebar
      :open="requestDrawerOpen"
      :folder="folder"
      :files="files"
      :mode="requestMode"
      :selected-file="selectedFile"
      :support-summary="supportSummary"
      @close="emit('close-request')"
      @update:mode="emit('update:requestMode', $event)"
      @select-file="emit('select-file', $event)"
      @clear-selected-file="emit('clear-selected-file')"
      @request-created="emit('request-created')"
    />

    <AssignmentChatWidget
      :current-user-id="currentUserId"
      title="Messages"
    />
  </div>
</template>
