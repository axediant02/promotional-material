<script setup>
import NavBar from '../components/NavBar.vue'
import HeroSection from '../components/HeroSection.vue'
import StatsSection from '../components/StatsSection.vue'
import ProblemSection from '../components/ProblemSection.vue'
import FeatureSection from '../components/FeatureSection.vue'
import RoleSection from '../components/RoleSection.vue'
import WorkflowSummarySection from '../components/WorkflowSummarySection.vue'
import FeatureListSection from '../components/FeatureListSection.vue'
import FooterSection from '../components/FooterSection.vue'
</script>

<template>
  <main id="top" class="pm-page pm-landing-page relative text-ink">

    <NavBar />

    <div class="relative overflow-hidden">
      <HeroSection />
      <StatsSection />
      <ProblemSection />
      <FeatureSection />
      <RoleSection />
      <FeatureListSection />
      <WorkflowSummarySection />
    </div>

    <FooterSection />
  </main>
</template>
