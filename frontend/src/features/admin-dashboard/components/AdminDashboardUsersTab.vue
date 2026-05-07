<script setup>
import { reactive, watch } from 'vue'
import DashboardSectionHeader from '../../../components/shared/DashboardSectionHeader.vue'

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  savingUserId: {
    type: String,
    default: '',
  },
  creatingAgent: {
    type: Boolean,
    default: false,
  },
  updateRoleAction: {
    type: Function,
    required: true,
  },
  createAgentAction: {
    type: Function,
    required: true,
  },
})

const roleStyles = {
  admin: 'border border-red-200 bg-red-50 text-red-700 dark:border-red-400/40 dark:bg-red-500/15 dark:text-red-200',
  production: 'border border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-400/40 dark:bg-blue-500/15 dark:text-blue-200',
  agent: 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-400/40 dark:bg-emerald-500/15 dark:text-emerald-200',
  client: 'border border-[#d4d0dc] bg-[#f8f6fb] text-[#6b5f7a] dark:border-white/15 dark:bg-white/[0.06] dark:text-zinc-300',
}

const roleDrafts = reactive({})
const rowErrors = reactive({})
const rowFeedback = reactive({})
const agentForm = reactive({
  name: '',
  email: '',
  password: '',
})
const agentFormErrors = reactive({})
const agentFormFeedback = reactive({
  message: '',
})

watch(
  () => props.users,
  (users) => {
    for (const user of users) {
      if (user?.id) {
        roleDrafts[user.id] = user.role
      }
    }
  },
  { immediate: true, deep: true }
)

const updateDraft = (userId, role) => {
  roleDrafts[userId] = role
  rowErrors[userId] = ''
  rowFeedback[userId] = ''
}

const saveRole = async (user) => {
  if (!user?.id) {
    return
  }

  rowErrors[user.id] = ''
  rowFeedback[user.id] = ''

  try {
    await props.updateRoleAction(user.id, roleDrafts[user.id])
    rowFeedback[user.id] = 'Role updated.'
  } catch (error) {
    rowErrors[user.id] = error.response?.data?.errors?.role?.[0]
      ?? error.response?.data?.message
      ?? 'Unable to update the user role.'
  }
}

const resetAgentFormErrors = () => {
  agentFormErrors.name = ''
  agentFormErrors.email = ''
  agentFormErrors.password = ''
  agentFormErrors.form = ''
  agentFormFeedback.message = ''
}

const createAgent = async () => {
  if (props.creatingAgent) {
    return
  }

  resetAgentFormErrors()

  try {
    await props.createAgentAction({
      name: agentForm.name.trim(),
      email: agentForm.email.trim(),
      password: agentForm.password,
    })

    agentForm.name = ''
    agentForm.email = ''
    agentForm.password = ''
    agentFormFeedback.message = 'Agent account created.'
  } catch (error) {
    const errors = error.response?.data?.errors ?? {}
    agentFormErrors.name = errors.name?.[0] ?? ''
    agentFormErrors.email = errors.email?.[0] ?? ''
    agentFormErrors.password = errors.password?.[0] ?? ''
    agentFormErrors.form = error.response?.data?.message ?? 'Unable to create the agent account.'
  }
}
</script>

<template>
  <section class="space-y-6">
    <DashboardSectionHeader
      eyebrow="Access control"
      title="Users & roles"
      description="Review role ownership, account posture, and the current access mix across every dashboard audience."
      :badge="`${users.length} users`"
      compact
    />

    <form
      class="grid gap-4 border border-black/10 bg-white/65 p-5 dark:border-white/10 dark:bg-[#181818] lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(12rem,0.8fr)_auto]"
      @submit.prevent="createAgent"
    >
      <div>
        <label for="agent-name" class="text-[10px] font-semibold uppercase tracking-[0.24em] text-zinc-500">Agent name</label>
        <input
          id="agent-name"
          v-model="agentForm.name"
          class="pm-input mt-2 w-full rounded-xl px-3 py-2 text-sm"
          type="text"
          autocomplete="name"
          placeholder="Agent User"
          :disabled="creatingAgent"
        >
        <p v-if="agentFormErrors.name" class="mt-2 text-xs text-red-600 dark:text-red-300">{{ agentFormErrors.name }}</p>
      </div>

      <div>
        <label for="agent-email" class="text-[10px] font-semibold uppercase tracking-[0.24em] text-zinc-500">Email</label>
        <input
          id="agent-email"
          v-model="agentForm.email"
          class="pm-input mt-2 w-full rounded-xl px-3 py-2 text-sm"
          type="email"
          autocomplete="email"
          placeholder="agent@example.com"
          :disabled="creatingAgent"
        >
        <p v-if="agentFormErrors.email" class="mt-2 text-xs text-red-600 dark:text-red-300">{{ agentFormErrors.email }}</p>
      </div>

      <div>
        <label for="agent-password" class="text-[10px] font-semibold uppercase tracking-[0.24em] text-zinc-500">Password</label>
        <input
          id="agent-password"
          v-model="agentForm.password"
          class="pm-input mt-2 w-full rounded-xl px-3 py-2 text-sm"
          type="password"
          autocomplete="new-password"
          placeholder="Minimum 8 characters"
          :disabled="creatingAgent"
        >
        <p v-if="agentFormErrors.password" class="mt-2 text-xs text-red-600 dark:text-red-300">{{ agentFormErrors.password }}</p>
      </div>

      <div class="flex flex-col justify-end gap-2">
        <button
          type="submit"
          class="inline-flex justify-center rounded-full border border-border bg-white/70 px-4 py-2.5 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 transition hover:border-brand-500 hover:bg-brand-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-white/10 dark:text-white dark:hover:border-white/20 dark:hover:bg-white/15"
          :disabled="creatingAgent"
        >
          {{ creatingAgent ? 'Creating...' : 'Create agent' }}
        </button>
        <p v-if="agentFormErrors.form" class="text-xs text-red-600 dark:text-red-300">{{ agentFormErrors.form }}</p>
        <p v-else-if="agentFormFeedback.message" class="text-xs text-emerald-700 dark:text-emerald-300">{{ agentFormFeedback.message }}</p>
      </div>
    </form>

    <section class="overflow-hidden border border-black/10 bg-white/65 dark:border-white/10 dark:bg-[#181818]">
      <header class="hidden grid-cols-[minmax(0,1.25fr)_minmax(9rem,0.8fr)_minmax(7rem,0.6fr)_minmax(0,1fr)_auto] gap-4 border-b border-black/10 px-5 py-4 text-[10px] font-semibold uppercase tracking-[0.28em] text-zinc-500 dark:border-white/10 lg:grid">
        <span>User</span>
        <span>Role</span>
        <span>Status</span>
        <span>Notes</span>
        <span class="text-right">Action</span>
      </header>

      <article
        v-for="user in users"
        :key="user.id"
        class="border-b border-black/10 px-5 py-5 last:border-b-0 dark:border-white/10 lg:grid lg:grid-cols-[minmax(0,1.25fr)_minmax(9rem,0.8fr)_minmax(7rem,0.6fr)_minmax(0,1fr)_auto] lg:gap-4"
      >
        <div>
          <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ user.name }}</p>
          <p class="mt-1 text-sm text-zinc-500">{{ user.email }}</p>
          <p class="mt-2 text-[10px] uppercase tracking-[0.22em] text-zinc-500">{{ user.id }}</p>
        </div>

        <div class="mt-4 lg:mt-0">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-zinc-500 lg:hidden">Role</p>
          <div class="space-y-2">
            <span :class="['inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.22em]', roleStyles[user.role] ?? roleStyles.client]">
              {{ user.role }}
            </span>
            <select
              class="pm-input w-full rounded-xl px-3 py-2 text-xs font-semibold uppercase tracking-[0.18em]"
              :value="roleDrafts[user.id] ?? user.role"
              :disabled="user.isCurrentUser || savingUserId === user.id"
              @change="updateDraft(user.id, $event.target.value)"
            >
              <option value="admin">Admin</option>
              <option value="production">Production</option>
              <option value="agent">Agent</option>
              <option value="client">Client</option>
            </select>
          </div>
        </div>

        <div class="mt-4 lg:mt-0">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-zinc-500 lg:hidden">Status</p>
          <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ user.status || 'Not exposed' }}</p>
        </div>

        <div class="mt-4 lg:mt-0">
          <p class="mb-1 text-[10px] uppercase tracking-[0.2em] text-zinc-500 lg:hidden">Notes</p>
          <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ user.note }}</p>
          <p v-if="rowErrors[user.id]" class="mt-2 text-xs text-red-600 dark:text-red-300">{{ rowErrors[user.id] }}</p>
          <p v-else-if="rowFeedback[user.id]" class="mt-2 text-xs text-emerald-700 dark:text-emerald-300">{{ rowFeedback[user.id] }}</p>
        </div>

        <div class="mt-4 flex items-start lg:mt-0 lg:justify-end">
          <button
            type="button"
            class="inline-flex rounded-full border border-border bg-white/70 px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-700 transition hover:border-brand-500 hover:bg-brand-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-white/10 dark:text-white dark:hover:border-white/20 dark:hover:bg-white/15"
            :disabled="user.isCurrentUser || savingUserId === user.id || (roleDrafts[user.id] ?? user.role) === user.role"
            @click="saveRole(user)"
          >
            {{
              user.isCurrentUser
                ? 'Current admin'
                : savingUserId === user.id
                  ? 'Saving...'
                  : 'Save role'
            }}
          </button>
        </div>
      </article>
    </section>
  </section>
</template>
