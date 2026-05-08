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
  <main class="relative overflow-hidden bg-[#f5f1ea] text-slate-900">
    <div
      class="pointer-events-none absolute inset-x-0 top-0 h-[1080px] bg-[radial-gradient(circle_at_top,_rgba(217,119,6,0.14),_transparent_42%),radial-gradient(circle_at_20%_15%,_rgba(37,99,235,0.12),_transparent_30%),radial-gradient(circle_at_85%_0%,_rgba(15,23,42,0.08),_transparent_24%)]"
    />

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
