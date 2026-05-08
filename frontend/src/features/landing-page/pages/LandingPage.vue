<script setup>
import NavBar from '../components/NavBar.vue'
import HeroSection from '../components/HeroSection.vue'
import StatsSection from '../components/StatsSection.vue'
import ProblemSection from '../components/ProblemSection.vue'
import FeatureSection from '../components/FeatureSection.vue'
import RoleSection from '../components/RoleSection.vue'
import CtaSection from '../components/CtaSection.vue'
import FeatureListSection from '../components/FeatureListSection.vue'
import FooterSection from '../components/FooterSection.vue'
</script>

<template>
  <main class="pm-page pm-landing-page relative overflow-hidden text-ink">

    <NavBar />

    <div class="relative">
      <HeroSection />
      <StatsSection />
      <ProblemSection />
      <FeatureSection />
      <RoleSection />
      <FeatureListSection />
      <CtaSection />
    </div>

    <FooterSection />
  </main>
</template>
