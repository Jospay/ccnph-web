<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Wayfinder import (adjust if your import path differs)
import { index as sellerDashboard } from '@/actions/App/Http/Controllers/Web/Seller/DashboardController';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link href="/" class="text-xl font-bold text-indigo-600">
                                <!-- Coop Platform -->
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link 
                                :href="sellerDashboard().url" 
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none transition duration-150 ease-in-out"
                            >
                                Dashboard
                            </Link>
                            
                            <Link 
                                href="#" 
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out"
                            >
                                My Services
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="text-sm text-gray-500">
                            Member Account
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>