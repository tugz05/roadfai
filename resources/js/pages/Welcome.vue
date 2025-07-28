<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Map from '@/pages/placeholders/Map.vue'
</script>

<template>
  <Head title="Welcome - RoadFAI" />

  <!-- Background Video Layer -->
  <div class="relative min-h-screen overflow-hidden">
    <video
      autoplay
      muted
      loop
      class="absolute inset-0 object-cover w-full h-full z-0 opacity-50 grayscale"
      style="filter: blur(2px);"
    >
      <source src="/videos/smart-city.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>

    <!-- Main Overlay -->
    <div class="relative z-10 min-h-screen flex flex-col bg-gradient-to-br from-black/80 via-black/70 to-neutral-800/80 text-white font-sans backdrop-blur-md">

      <!-- Header with Logo -->
      <header class="w-full border-b border-white/10 shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
          <div class="flex items-center gap-3">
            <img src="/logo/logo.png" alt="RoadFAI Logo" class="w-10 h-10 rounded-xl shadow" />
            <span class="text-3xl font-bold tracking-wide text-white drop-shadow">RoadFAI</span>
          </div>
          <nav class="flex items-center gap-4 text-base font-semibold">
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="px-5 py-2 rounded-full hover:bg-white/10 focus:outline-none transition"
            >
              Dashboard
            </Link>
            <template v-else>
              <Link
                :href="route('login')"
                class="hover:text-blue-300 transition px-4 py-2"
              >
                Log in
              </Link>
              <Link
                :href="route('register')"
                class="bg-gradient-to-r from-blue-400 to-sky-500 text-white px-6 py-2 rounded-full shadow-lg hover:from-blue-500 hover:to-sky-600 transition font-bold"
              >
                Register
              </Link>
            </template>
          </nav>
        </div>
      </header>

      <!-- Hero Section -->
      <main class="flex-1 flex flex-col items-center justify-center text-center px-6 py-28 relative">
        <div class="max-w-3xl mx-auto bg-black/40 rounded-2xl p-10 shadow-xl backdrop-blur-lg border border-white/10">
          <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-6 leading-tight drop-shadow">
            <span class="bg-gradient-to-r from-blue-400 to-sky-500 bg-clip-text text-transparent">
              Clear Data,
            </span>
            <br />
            Better Road,
            <span class="block text-sky-400">Safer Zone</span>
          </h1>
          <p class="text-lg md:text-xl max-w-2xl mx-auto text-white/80 mb-10 font-medium">
            RoadFAI empowers Butuan City through a real-time road monitoring system powered by sensors and intelligent analytics—giving decision-makers the insight to plan, prioritize, and protect.
          </p>
          <div class="flex flex-col sm:flex-row gap-5 justify-center mt-6">
            <Link
              :href="route('register')"
              class="bg-gradient-to-r from-blue-400 to-sky-500 hover:from-blue-500 hover:to-sky-600 text-white px-8 py-3 rounded-full text-lg font-semibold shadow-xl transition"
            >
              Get Started
            </Link>
            <Link
              :href="route('login')"
              class="border-2 border-white text-white hover:bg-white/10 px-8 py-3 rounded-full text-lg font-semibold transition"
            >
              Learn More
            </Link>
          </div>
        </div>
      </main>

      <!-- Details Section -->
      <section class="py-20 px-6 bg-black/60 border-t border-b border-white/10">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-16">
          <div>
            <h2 class="text-3xl font-bold mb-6 text-sky-400">What is RoadFAI?</h2>
            <p class="text-lg text-white/90 leading-relaxed mb-4">
              RoadFAI is a smart infrastructure management platform tackling Butuan City’s toughest road monitoring challenges. It merges real-time mapping, AI-powered forecasting, and automated prioritization to keep roads safer and cities smarter. From geo-tagged reports to embedded IoT sensors, RoadFAI gives every stakeholder—from city planners to citizens—actionable data, live alerts, and peace of mind.
            </p>
          </div>

          <div>
            <h2 class="text-3xl font-bold mb-6 text-sky-400">Key Technologies</h2>
            <ul class="space-y-3 text-white/80 text-lg pl-3">
              <li><span class="font-bold text-sky-400">•</span> Arduino-based vehicle & road condition units</li>
              <li><span class="font-bold text-sky-400">•</span> Laravel + Vue + Inertia responsive platform</li>
              <li><span class="font-bold text-sky-400">•</span> Real-time data visualizations & mapping</li>
              <li><span class="font-bold text-sky-400">•</span> Instant alerts for hazardous conditions</li>
              <li><span class="font-bold text-sky-400">•</span> AI-driven prediction & optimization</li>
            </ul>
          </div>

          <div>
            <h2 class="text-3xl font-bold mb-6 text-sky-400">Who Uses RoadFAI?</h2>
            <ul class="space-y-3 text-white/80 text-lg pl-3">
              <li><span class="font-bold text-sky-400">•</span> <strong>City Planning Office</strong> — data-driven budgeting</li>
              <li><span class="font-bold text-sky-400">•</span> <strong>Engineering Office</strong> — targeted repairs</li>
              <li><span class="font-bold text-sky-400">•</span> <strong>Disaster Units</strong> — live road safety</li>
              <li><span class="font-bold text-sky-400">•</span> <strong>Citizens</strong> — transparent tracking</li>
            </ul>
          </div>

          <div>
            <h2 class="text-3xl font-bold mb-6 text-sky-400">Key Benefits</h2>
            <ul class="space-y-3 text-white/80 text-lg pl-3">
              <li><span class="font-bold text-sky-400">•</span> Reduced road accidents via predictive alerts</li>
              <li><span class="font-bold text-sky-400">•</span> Improved traffic & congestion control</li>
              <li><span class="font-bold text-sky-400">•</span> Transparent and efficient public service</li>
              <li><span class="font-bold text-sky-400">•</span> Optimized maintenance scheduling</li>
              <li><span class="font-bold text-sky-400">•</span> Supporting smart city goals</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Live Monitoring Preview -->
      <div class="bg-black/80 text-center py-20 px-6 border-b border-white/10">
        <h2 class="text-2xl md:text-3xl font-bold text-sky-400 mb-5">Live Monitoring Preview</h2>
        <div class="rounded-3xl max-w-5xl mx-auto h-72 md:h-96 bg-gradient-to-br from-neutral-900 via-black/90 to-black/80 border-2 border-dashed border-sky-400/40 shadow-2xl flex items-center justify-center">
          <Map
            :landuse-kml-url="'/kml/landuse_KML.kml'"
            :roads-kml-url="'/kml/TNDG_ROADNETWORKS_KML.kml'"
          />
        </div>
      </div>

      <!-- Footer -->
      <footer class="bg-black/95 text-center text-base text-white/50 py-8 border-t border-white/10 tracking-wide">
        &copy; 2025 <span class="font-bold text-white">RoadFAI</span> — Intelligent Infrastructure Monitoring for Butuan City.
      </footer>
    </div>
  </div>
</template>
