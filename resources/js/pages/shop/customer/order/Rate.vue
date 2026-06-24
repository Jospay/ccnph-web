<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
  ArrowLeft, 
  Star, 
  Camera, 
  Store, 
  X, 
  UploadCloud
} from 'lucide-vue-next';
import { ref } from 'vue';
import UserAccountSidebar from '@/components/accounts/UserAccountSidebar.vue';
import Footer from '@/components/sections/Footer.vue';
import Navbar from '@/components/sections/Navbar.vue';
import TopBar from '@/components/sections/TopBar.vue';
import shop from '@/routes/shop';

interface OrderItem {
  id: number;
  product_name: string;
  product_image: string | null;
  variant_name: string | null;
}

interface OrderDetails {
  id: number;
  order_number: string;
  store: {
    id: number;
    name: string;
  };
  items: OrderItem[];
}

const props = defineProps<{
  user: { name: string; avatar: string | null };
  order: OrderDetails;
}>();

// Initialize Inertia form tracking state for each item dynamically
const form = useForm({
  items: props.order.items.map((item) => ({
    order_item_id: item.id,
    rating: 5, // Default to a 5-star rating
    comment: '',
  })),
});

// Explicit reactive hover tracking dictionary for star interactions
const hoverRatings = ref<Record<number, number>>({});

const setRating = (itemIndex: number, score: number) => {
  form.items[itemIndex].rating = score;
};

const setHoverRating = (itemIndex: number, score: number) => {
  hoverRatings.value[itemIndex] = score;
};

const clearHoverRating = (itemIndex: number) => {
  delete hoverRatings.value[itemIndex];
};

const submitReview = () => {
  // Replace with your designated post route handler block
  form.post(`/orders/${props.order.id}/rate`, {
    preserveScroll: true,
    onSuccess: () => {
      // Handle completion logic (e.g., redirect or flash toast notice)
    },
  });
};
</script>

<template>
  <Head :title="`Rate Order #${order.order_number}`" />

  <div class="flex min-h-screen flex-col transition-colors duration-300">
    <TopBar />
    <div class="sticky top-0 z-50 mt-8">
      <Navbar />
    </div>

    <main class="mx-auto w-full max-w-7xl flex-grow px-4 py-8 sm:px-6 md:py-12 lg:px-8">
      <div class="flex flex-col gap-8 lg:flex-row">
        <UserAccountSidebar :name="user.name" :avatar="user.avatar" />

        <div class="min-w-0 flex-1">
          <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <Link 
              :href="shop.orders.index.url()" 
              class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
            >
              <ArrowLeft class="h-4 w-4" /> Back to Purchases
            </Link>
            <div class="text-sm text-zinc-500">
              Order No: <span class="font-bold text-zinc-800 dark:text-white">#{{ order.order_number }}</span>
            </div>
          </div>

          <form @submit.prevent="submitReview" class="space-y-6">
            
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900 flex items-center gap-2">
              <Store class="h-5 w-5 text-zinc-400" />
              <span class="text-sm font-black text-zinc-800 dark:text-white">Reviewing Store: {{ order.store?.name }}</span>
            </div>

            <div 
              v-for="(item, index) in order.items" 
              :key="item.id"
              class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 space-y-6"
            >
              <div class="flex gap-4 border-b border-zinc-100 pb-4 dark:border-zinc-800">
                <img 
                  :src="item.product_image || '/placeholder-product.png'" 
                  :alt="item.product_name"
                  class="h-16 w-16 shrink-0 rounded-xl border border-zinc-200 object-cover dark:border-zinc-700"
                />
                <div class="min-w-0 flex-1">
                  <h4 class="font-bold text-zinc-900 dark:text-white line-clamp-1">{{ item.product_name }}</h4>
                  <p v-if="item.variant_name" class="mt-0.5 text-xs text-zinc-400">Variation: {{ item.variant_name }}</p>
                </div>
              </div>

              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-6">
                <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Product Quality:</span>
                <div class="flex items-center gap-1.5">
                  <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    @click="setRating(index, star)"
                    @mouseenter="setHoverRating(index, star)"
                    @mouseleave="clearHoverRating(index)"
                    class="cursor-pointer p-0.5 transition-transform hover:scale-110 focus:outline-none"
                  >
                    <Star 
                      class="h-7 w-7 transition-colors" 
                      :class="[
                        star <= (hoverRatings[index] ?? form.items[index].rating)
                          ? 'fill-amber-400 text-amber-400'
                          : 'text-zinc-300 dark:text-zinc-700'
                      ]"
                    />
                  </button>
                  <span class="ml-2 text-xs font-bold uppercase text-amber-500">
                    {{ 
                      ['Terrible', 'Poor', 'Fair', 'Good', 'Excellent'][
                        (hoverRatings[index] ?? form.items[index].rating) - 1
                      ] 
                    }}
                  </span>
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Share your review</label>
                <textarea
                  v-model="form.items[index].comment"
                  rows="4"
                  placeholder="Tell others about the product quality, shipping speed, packaging details..."
                  class="w-full rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 text-sm text-zinc-900 shadow-sm outline-none transition-all focus:border-[#009933] focus:ring-2 focus:ring-[#009933]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <Link
                :href="shop.orders.index.url()"
                class="rounded-xl border border-zinc-200 bg-white px-6 py-3 text-sm font-bold text-zinc-600 hover:bg-zinc-50 transition-colors dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 cursor-pointer"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-xl bg-[#009933] px-8 py-3 text-sm font-bold text-white hover:bg-[#007722] transition-colors disabled:opacity-50 cursor-pointer shadow-sm"
              >
                {{ form.processing ? 'Submitting...' : 'Submit Review' }}
              </button>
            </div>

          </form>
        </div>
      </div>
    </main>
    <Footer />
  </div>
</template>