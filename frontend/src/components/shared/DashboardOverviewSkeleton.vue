<script setup>
import SkeletonBlock from './SkeletonBlock.vue'

defineProps({
  statCount: {
    type: Number,
    default: 4,
  },
  attentionCount: {
    type: Number,
    default: 3,
  },
  queueRowCount: {
    type: Number,
    default: 4,
  },
  sideCardCount: {
    type: Number,
    default: 3,
  },
  insightCount: {
    type: Number,
    default: 3,
  },
})
</script>

<template>
  <div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <article
        v-for="stat in statCount"
        :key="`dashboard-stat-skeleton-${stat}`"
        class="pm-surface rounded-[1.8rem] p-5"
      >
        <SkeletonBlock width="w-24" height="h-3" />
        <SkeletonBlock width="w-20" height="h-9" rounded="rounded-md" class-name="mt-4" />
        <SkeletonBlock width="w-28" height="h-3" class-name="mt-4" />
      </article>
    </section>

    <section class="pm-surface rounded-[1.9rem] p-6">
      <div class="flex items-center justify-between gap-4">
        <div class="space-y-3">
          <SkeletonBlock width="w-28" height="h-3" />
          <SkeletonBlock width="w-48" height="h-6" rounded="rounded-md" />
        </div>
        <SkeletonBlock width="w-24" height="h-8" rounded="rounded-full" />
      </div>

      <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div
          v-for="panel in attentionCount"
          :key="`dashboard-attention-skeleton-${panel}`"
          class="rounded-[1.5rem] border border-border/70 p-4 dark:border-white/10"
        >
          <SkeletonBlock width="w-24" height="h-3" />
          <SkeletonBlock width="w-14" height="h-8" rounded="rounded-md" class-name="mt-4" />
          <SkeletonBlock width="w-full" height="h-3" class-name="mt-4" />
          <SkeletonBlock width="w-4/5" height="h-3" class-name="mt-2" />
        </div>
      </div>
    </section>

    <section class="grid gap-8 xl:grid-cols-[minmax(0,1.7fr)_minmax(20rem,0.92fr)]">
      <article class="pm-surface rounded-[1.9rem] p-6">
        <div class="flex items-center justify-between gap-4">
          <div class="space-y-3">
            <SkeletonBlock width="w-24" height="h-3" />
            <SkeletonBlock width="w-40" height="h-6" rounded="rounded-md" />
          </div>
          <SkeletonBlock width="w-20" height="h-8" rounded="rounded-full" />
        </div>

        <div class="mt-6 space-y-4">
          <div
            v-for="row in queueRowCount"
            :key="`dashboard-queue-skeleton-${row}`"
            class="rounded-[1.4rem] border border-border/70 p-4 dark:border-white/10"
          >
            <SkeletonBlock width="w-20" height="h-3" />
            <SkeletonBlock width="w-2/3" height="h-5" rounded="rounded-md" class-name="mt-4" />
            <SkeletonBlock width="w-full" height="h-3" class-name="mt-3" />
            <SkeletonBlock width="w-5/6" height="h-3" class-name="mt-2" />
            <div class="mt-4 flex gap-3">
              <SkeletonBlock width="w-24" height="h-8" rounded="rounded-full" />
              <SkeletonBlock width="w-20" height="h-8" rounded="rounded-full" />
            </div>
          </div>
        </div>
      </article>

      <div class="space-y-6">
        <article class="pm-surface rounded-[1.9rem] p-6">
          <SkeletonBlock width="w-24" height="h-3" />
          <SkeletonBlock width="w-36" height="h-6" rounded="rounded-md" class-name="mt-4" />
          <div class="mt-6 space-y-4">
            <div
              v-for="card in sideCardCount"
              :key="`dashboard-side-card-skeleton-${card}`"
              class="rounded-[1.4rem] border border-border/70 p-4 dark:border-white/10"
            >
              <SkeletonBlock width="w-2/3" height="h-4" />
              <SkeletonBlock width="w-1/2" height="h-3" class-name="mt-3" />
            </div>
          </div>
        </article>

        <article class="pm-surface rounded-[1.9rem] p-6">
          <SkeletonBlock width="w-28" height="h-3" />
          <SkeletonBlock width="w-32" height="h-6" rounded="rounded-md" class-name="mt-4" />
          <div class="mt-6 space-y-3">
            <SkeletonBlock
              v-for="insight in insightCount"
              :key="`dashboard-insight-skeleton-${insight}`"
              width="w-full"
              height="h-12"
              rounded="rounded-[1.2rem]"
            />
          </div>
        </article>
      </div>
    </section>
  </div>
</template>
