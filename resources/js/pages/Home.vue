<script setup lang="ts">
// defineOptions({ layout: null as any });
// import { Head, Link } from '@inertiajs/vue3';
// import { dashboard, login, register } from '@/routes';
import AboutSection from '@/components/landing/AboutSection.vue';
import ContactSection from '@/components/landing/ContactSection.vue';
import Footer from '@/components/landing/Footer.vue';
import GallerySection from '@/components/landing/GallerySection.vue';
import HomeSection from '@/components/landing/HomeSection.vue';
import Navbar from '@/components/landing/Navbar.vue';
import NewsUpdates from '@/components/landing/NewsUpdates.vue';
import ProgramServices from '@/components/landing/ProgramServices.vue';
import StrategicPlans from '@/components/landing/StrategicPlans.vue';
import TestimonialsSection from '@/components/landing/TestimonialsSection.vue';

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
    <HomeSection :data="$page.props.home" />
    <AboutSection :data="$page.props.about" />
    <ProgramServices />
    <StrategicPlans />
    <TestimonialsSection />
    <NewsUpdates />
    <GallerySection />
    <ContactSection />
    <Footer />
    </main>
</template>