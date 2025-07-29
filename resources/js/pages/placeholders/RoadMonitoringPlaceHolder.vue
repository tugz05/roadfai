<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import mapboxgl from 'mapbox-gl';
import * as toGeoJSON from '@tmcw/togeojson';
import { DOMParser } from 'xmldom';

interface KmlLayer {
  url: string;
  color: string;
}
const props = defineProps<{
  kmlLayers: KmlLayer[];
  features: any[];
  highlightFeature?: any | null;
  lat?: number;
  lng?: number;
  zoom?: number;
}>();

const DEFAULT_LAT = 8.9505;
const DEFAULT_LNG = 125.5435;
const DEFAULT_ZOOM = 12;

const mapContainer = ref<HTMLDivElement | null>(null);
let map: mapboxgl.Map | null = null;
let statusMarkers: mapboxgl.Marker[] = [];
let highlightMarker: mapboxgl.Marker | null = null;

mapboxgl.accessToken = 'pk.eyJ1Ijoibm9vYnR1Z3oiLCJhIjoiY21kZHdvaDZ3MDkxMDJscHFjbTNzbWNkciJ9.pUXsJVW32G9zpHypFNT1-g';

function removeAllMarkers() {
  for (const m of statusMarkers) m.remove();
  statusMarkers = [];
  if (highlightMarker) highlightMarker.remove();
  highlightMarker = null;
}

function addStatusMarkers() {
  removeAllMarkers();
  for (const feature of props.features) {
    let color = '#22c55e', animate = '';
    if (feature.status === 'Hazard') { color = '#ef4444'; animate = 'blink 1s linear infinite'; }
    else if (feature.status === 'Needs Maintenance') { color = '#f59e42'; animate = 'pulse 1.4s infinite'; }
    else if (feature.status === 'Offline') { color = '#64748b'; animate = 'blink 2s linear infinite'; }
    const el = document.createElement('div');
    el.className = 'custom-blink-marker';
    el.style.width = '26px';
    el.style.height = '26px';
    el.style.borderRadius = '50%';
    el.style.background = color;
    el.style.boxShadow = '0 0 0 4px #fff';
    el.style.animation = animate;
    el.style.border = '2.5px solid #fff';
    el.title = feature.roadName + ' - ' + feature.status;
    if (feature.status === 'Hazard') {
      el.innerHTML = `<svg width="20" height="20" style="margin:2px 3px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#fff7" /><path d="M12 6v6" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="16" r="1" fill="#ef4444"/></svg>`;
    }
    const marker = new mapboxgl.Marker(el)
      .setLngLat(feature.center)
      .addTo(map!);

    marker.getElement().addEventListener('click', (e) => {
      e.stopPropagation();
      showPopup(feature);
    });
    statusMarkers.push(marker);
  }
}

function showPopup(feature: any) {
  let html = `
    <div style="font-weight:700;font-size:1.07rem;margin-bottom:5px;">${feature.roadName}</div>
    <div style="font-size:13px;"><b>Status:</b> <span style="color:${feature.status === 'Hazard' ? '#ef4444' : feature.status === 'Needs Maintenance' ? '#f59e42' : '#22c55e'};">${feature.status}</span></div>
    <div style="font-size:13px;"><b>Type:</b> ${feature.roadType}</div>
    <div style="font-size:13px;"><b>Location:</b> ${feature.location}</div>
    <div style="font-size:13px;"><b>Barangay:</b> ${feature.barangay}</div>
    <div style="font-size:13px;"><b>Last Updated:</b> ${feature.lastUpdated}</div>
    <div style="font-size:13px;"><b>Sensor Data:</b> Cracks: ${feature.sensor?.cracks ?? 'n/a'} | Traffic: ${feature.sensor?.traffic ?? 'n/a'} | Temp: ${feature.sensor?.temp ?? 'n/a'}</div>
    <div style="font-size:13px;margin:8px 0 5px 0;"><b>AI Analysis:</b> <span style="color:#3b82f6">${feature.aiAnalysis}</span></div>
    <div style="font-size:13px;"><b>Geotagged Photos:</b><div style="display:flex;gap:6px;margin-top:5px;">${
      feature.photos?.map((photo:any) => `<img src="${photo.img}" alt="${photo.name}" title="${photo.name}" style="width:80px;height:56px;border-radius:7px;object-fit:cover;border:1px solid #eee;">`).join('')
    }</div></div>
  `;
  new mapboxgl.Popup().setLngLat(feature.center).setHTML(html).addTo(map!);
}

// Focus to highlighted feature when it changes
watch(() => props.highlightFeature, (feature) => {
  if (!map || !feature) return;
  if (highlightMarker) highlightMarker.remove();
  const el = document.createElement('div');
  el.style.width = '40px';
  el.style.height = '40px';
  el.style.borderRadius = '50%';
  el.style.background = 'rgba(59,130,246,0.17)';
  el.style.boxShadow = '0 0 0 12px #3b82f6bb';
  el.style.border = '3px solid #2563eb';
  highlightMarker = new mapboxgl.Marker(el).setLngLat(feature.center).addTo(map!);
  map.flyTo({
    center: feature.center,
    zoom: 16,
    speed: 1.3,
    curve: 1,
    essential: true
  });
  showPopup(feature);
});

onMounted(async () => {
  if (!mapContainer.value) return;
  map = new mapboxgl.Map({
    container: mapContainer.value,
    style: 'mapbox://styles/mapbox/satellite-streets-v11',
    center: [props.lng ?? DEFAULT_LNG, props.lat ?? DEFAULT_LAT],
    zoom: props.zoom ?? DEFAULT_ZOOM,
  });
  map.addControl(new mapboxgl.NavigationControl());
  map.on('load', async () => {
    if (props.kmlLayers && props.kmlLayers.length) {
      for (let i = 0; i < props.kmlLayers.length; i++) {
        const { url, color } = props.kmlLayers[i];
        await loadKmlDynamicLayer(i, url, color);
      }
    }
    addStatusMarkers();
  });
});
onBeforeUnmount(() => {
  removeAllMarkers();
  map?.remove();
  map = null;
});

async function loadKmlDynamicLayer(idx: number, kmlUrl: string, color: string) {
  const id = `kml-${idx}`;
  const res = await fetch(kmlUrl);
  const kmlText = await res.text();
  const parser = new DOMParser();
  const kmlDoc = parser.parseFromString(kmlText, 'text/xml');
  const geojson = toGeoJSON.kml(kmlDoc);
  if (!geojson.features.length) return;
  map!.addSource(id, { type: 'geojson', data: geojson });
  const firstGeom = geojson.features[0].geometry?.type;
  if (firstGeom === 'Polygon' || firstGeom === 'MultiPolygon') {
    map!.addLayer({
      id: `${id}-fill`,
      type: 'fill',
      source: id,
      paint: { 'fill-color': color, 'fill-opacity': 0.32 }
    });
    map!.addLayer({
      id: `${id}-border`,
      type: 'line',
      source: id,
      paint: { 'line-color': '#1a2637', 'line-width': 1.3 }
    });
  }
  if (firstGeom === 'LineString' || firstGeom === 'MultiLineString') {
    map!.addLayer({
      id: `${id}-line`,
      type: 'line',
      source: id,
      paint: { 'line-color': color, 'line-width': 3, 'line-dasharray': [2, 2] }
    });
  }
  if (firstGeom === 'Point' || firstGeom === 'MultiPoint') {
    map!.addLayer({
      id: `${id}-points`,
      type: 'circle',
      source: id,
      paint: {
        'circle-color': color,
        'circle-radius': 4,
        'circle-stroke-width': 1,
        'circle-stroke-color': '#fff',
      }
    });
  }
}
</script>

<template>
  <div ref="mapContainer" class="w-full h-[400px] md:h-[600px] rounded-xl border" />
</template>

<style>
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.2; } }
@keyframes pulse { 0% { box-shadow: 0 0 0 0 #f59e4290; } 50% { box-shadow: 0 0 0 12px #f59e4200; } 100% { box-shadow: 0 0 0 0 #f59e4290; } }
.custom-blink-marker { transition: box-shadow 0.18s; }
</style>
