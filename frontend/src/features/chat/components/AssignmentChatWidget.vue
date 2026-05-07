<script setup>
import { ref } from 'vue'
import ClientChatDrawer from '../../client-dashboard/components/ClientChatDrawer.vue'
import ClientChatLauncher from '../../client-dashboard/components/ClientChatLauncher.vue'

defineProps({
  currentUserId: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: 'Messages',
  },
})

const open = ref(false)
const badgeCount = ref(0)
</script>

<template>
  <ClientChatLauncher
    v-if="!open"
    :badge-count="badgeCount"
    :open="open"
    @toggle="open = !open"
  />

  <ClientChatDrawer
    :open="open"
    :current-user-id="currentUserId"
    :title="title"
    @close="open = false"
    @unread-count-change="badgeCount = $event"
  />
</template>
