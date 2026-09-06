<script setup lang="ts">
import About from '@/components/landing/About.vue';
import CcnphEcosystem from '@/components/landing/CcnphEcosystem.vue';
import ConnectWithUs from '@/components/landing/ConnectWithUs.vue';
import Footer from '@/components/landing/Footer.vue';
import HomeSection from '@/components/landing/HomeSection.vue';
import MissionVision from '@/components/landing/MissionVision.vue';
import Navbar from '@/components/landing/Navbar.vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Navbar :can-register="canRegister" />
    <main>
    <HomeSection id="home" :data="$page.props.home" />
    <About id="about" />
    <MissionVision />
    <CcnphEcosystem />
    <ConnectWithUs />

    <Footer />
    </main>
</template>
