<script setup>
import SkeletonBlock from './SkeletonBlock.vue'

defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  emptyMessage: { type: String, default: 'No data available' },
})

defineEmits(['action'])
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              :class="[
                'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600',
                col.class || ''
              ]"
            >
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          <template v-if="loading">
            <tr v-for="i in 5" :key="i">
              <td
                v-for="col in columns"
                :key="col.key"
                class="px-4 py-3"
              >
                <SkeletonBlock width="w-3/4" height="h-4" tone="bg-slate-200" />
              </td>
            </tr>
          </template>
          <template v-else-if="data.length === 0">
            <tr>
              <td
                :colspan="columns.length"
                class="px-4 py-8 text-center text-sm text-slate-500"
              >
                {{ emptyMessage }}
              </td>
            </tr>
          </template>
          <template v-else>
            <tr
              v-for="(row, idx) in data"
              :key="row.id || row[columns[0]?.key] || idx"
              class="transition-colors hover:bg-slate-50"
            >
              <td
                v-for="col in columns"
                :key="col.key"
                class="px-4 py-3 text-sm text-slate-700"
              >
                <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                  {{ row[col.key] }}
                </slot>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>
