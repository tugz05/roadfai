<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Map from '@/pages/placeholders/Map.vue'
</script>

<template>
  <Head title="Welcome - RoadFAI" />

  <!-- Background Video -->
  <div class="relative min-h-screen overflow-hidden">
    <video autoplay muted loop class="absolute inset-0 object-cover w-full h-full z-0 opacity-40 grayscale">
      <source src="/videos/smart-city.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>

    <!-- Overlay content -->
    <div class="relative z-10 min-h-screen flex flex-col bg-black/30 backdrop-blur-sm text-white font-sans">
      <!-- Header -->
      <header class="w-full border-b border-white/10">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
          <div class="text-2xl font-bold text-white">RoadFAI</div>
          <nav class="flex items-center gap-4 text-sm font-medium">
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="px-4 py-2 rounded hover:bg-white/10 transition"
            >
              Dashboard
            </Link>
            <template v-else>
              <Link
                :href="route('login')"
                class="hover:text-gray-300 transition"
              >
                Log in
              </Link>
              <Link
                :href="route('register')"
                class="border border-white text-white hover:bg-white hover:text-black px-4 py-2 rounded-md transition"
              >
                Register
              </Link>
            </template>
          </nav>
        </div>
      </header>

      <!-- Hero Section -->
      <main class="flex-1 flex flex-col items-center justify-center text-center px-6 py-20">
        <h1 class="text-5xl lg:text-6xl font-extrabold tracking-tight mb-6">
          Clear Data, Better Road, Safer Zone
        </h1>
        <p class="text-lg max-w-3xl text-white/80 mb-8">
          RoadFAI empowers Butuan City through a real-time road monitoring system using Arduino sensors and intelligent data analytics — giving the City Planning and Development Office the tools to plan, prioritize, and protect.
        </p>
        <div class="flex gap-4">
          <Link
            :href="route('register')"
            class="bg-white text-black hover:bg-gray-200 px-6 py-2 rounded-md text-sm font-semibold shadow transition"
          >
            Get Started
          </Link>
          <Link
            :href="route('login')"
            class="border border-white text-white hover:bg-white hover:text-black px-6 py-2 rounded-md text-sm font-semibold transition"
          >
            Learn More
          </Link>
        </div>
      </main>

      <!-- Details Section -->
      <section class="bg-black/70 text-white py-20 px-6">
        <div class="max-w-6xl mx-auto grid gap-12 lg:grid-cols-2">
          <div>
            <h2 class="text-3xl font-bold mb-4">What is RoadFAI?</h2>
            <p class="text-white/80">
              RoadFAI is a smart road infrastructure management system designed to address Butuan City’s persistent challenges in road monitoring and maintenance. It combines GIS mapping, real-time geo-location, AI-powered forecasting, and automated issue prioritization to deliver a data-driven solution for road repairs. Using a cross-platform, barangay officials/CEO can report road issues with geo-tagged photos, while embedded sensors detect environmental conditions such as excessive heat or flooding and send real-time alerts. All inputs are mapped and analyzed to assess severity and urgency. RoadFAI empowers city planners with actionable insights and predictive analytics, enabling faster response, smarter decisions, safer roads, and greater public trust.
            </p>
          </div>

          <div>
            <h2 class="text-3xl font-bold mb-4">Key Technologies</h2>
            <ul class="list-disc ml-6 space-y-2 text-white/80">
              <li>Arduino-based vehicle detection and road condition units</li>
              <li>Laravel + Vue + Inertia for a responsive web platform</li>
              <li>Data visualization using real-time charts/maps</li>
              <li>Alert system for hazardous road conditions</li>
              <li>Expandable with AI prediction models</li>
            </ul>
          </div>

          <div>
            <h2 class="text-3xl font-bold mb-4">Who Uses RoadFAI?</h2>
            <ul class="list-disc ml-6 space-y-2 text-white/80">
              <li><strong>City Planning Office</strong> – for data-driven planning and budgeting</li>
              <li><strong>Engineering Office</strong> – for identifying roads needing repairs</li>
              <li><strong>Disaster Response Units</strong> – for road safety alerts</li>
              <li><strong>Citizens</strong> – for transparent infrastructure tracking</li>
            </ul>
          </div>

          <div>
            <h2 class="text-3xl font-bold mb-4">Key Benefits</h2>
            <ul class="list-disc ml-6 space-y-2 text-white/80">
              <li>Reduced road accidents through predictive alerts</li>
              <li>Better traffic flow and congestion control</li>
              <li>Transparent and efficient public service</li>
              <li>Optimized road maintenance scheduling</li>
              <li>Supports Smart City transformation goals</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Placeholder for future dashboard/map -->
      <div class="bg-black/60 text-center py-16 px-6">
        <h2 class="text-2xl font-bold text-white mb-4">Live Monitoring Preview</h2>
        <div class="rounded-xl max-w-5xl mx-auto h-64 lg:h-96 bg-gray-900 border border-dashed border-white/20 flex items-center justify-center">
         <Map
                        :landuse-kml-url="'/kml/landuse_KML.kml'"
                        :roads-kml-url="'/kml/TNDG_ROADNETWORKS_KML.kml'"
                            />
        </div>
      </div>

      <!-- Footer -->
      <footer class="bg-black/90 text-center text-sm text-white/60 py-6 border-t border-white/10">
        &copy; 2025 <strong>RoadFAI</strong> — Intelligent Infrastructure Monitoring for Butuan City.
      </footer>
    </div>
  </div>
</template>
