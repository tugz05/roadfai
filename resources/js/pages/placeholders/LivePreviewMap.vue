<template>
  <div>
    <!-- Legend -->
    <div class="flex flex-wrap gap-3 mb-3">
      <div
        v-for="(layer, idx) in props.kmlLayers"
        :key="layer.url"
        class="flex items-center space-x-2"
      >
        <span
          class="inline-block w-5 h-5 rounded"
          :style="{ backgroundColor: layer.color, border: '1.5px solid #444' }"
        ></span>
        <span class="text-sm font-semibold">{{ getLayerLabel(layer.url) }}</span>
      </div>
    </div>

    <!-- Filter UI -->
    <div class="flex flex-wrap gap-4 mb-3">
      <label
        v-for="(layer, idx) in props.kmlLayers"
        :key="layer.url"
        class="inline-flex items-center space-x-2"
      >
        <input
          type="checkbox"
          v-model="visibleLayers[idx]"
          @change="toggleLayerVisibility(idx)"
        />
        <span :style="{ color: layer.color, fontWeight: 600 }">
          {{ getLayerLabel(layer.url) }}
        </span>
      </label>
    </div>

    <div ref="mapContainer" class="w-full h-full min-h-[400px] rounded-xl border" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import mapboxgl from 'mapbox-gl';
import * as toGeoJSON from '@tmcw/togeojson';
import { DOMParser } from 'xmldom';
import * as turf from '@turf/turf';

interface KmlLayer {
  url: string;
  color: string;
}
interface Props {
  kmlLayers: KmlLayer[];
  lat?: number;
  lng?: number;
  zoom?: number;
}
const props = defineProps<Props>();

const DEFAULT_LAT = 9.074968;
const DEFAULT_LNG = 126.201170;
const DEFAULT_ZOOM = 13;

const mapContainer = ref<HTMLDivElement | null>(null);
let map: mapboxgl.Map | null = null;
let highlightLayerId: string | null = null; // for road highlight

// Track visible checkboxes
const visibleLayers = ref<boolean[]>(props.kmlLayers.map(() => true));

// Track loaded MapboxGL layer IDs for each KML
const layerIdMap = ref<{ [key: number]: string[] }>({});

function getLayerLabel(url: string) {
  return url.split('/').pop()?.replace('.kml','').replace(/_/g, ' ') ?? url;
}

mapboxgl.accessToken = 'pk.eyJ1Ijoibm9vYnR1Z3oiLCJhIjoiY21kZHdvaDZ3MDkxMDJscHFjbTNzbWNkciJ9.pUXsJVW32G9zpHypFNT1-g';

// Remove any previous highlight
function removeHighlightLayer() {
  if (map && highlightLayerId && map.getLayer(highlightLayerId)) {
    map.removeLayer(highlightLayerId);
    highlightLayerId = null;
  }
  if (map && map.getSource('highlighted-road')) {
    map.removeSource('highlighted-road');
  }
}

// Add highlight for the selected road feature
function highlightRoad(feature: any) {
  removeHighlightLayer();

  if (!map) return;
  map.addSource('highlighted-road', {
    type: 'geojson',
    data: feature,
  });
  highlightLayerId = 'highlighted-road-line';
  map.addLayer({
    id: highlightLayerId,
    type: 'line',
    source: 'highlighted-road',
    paint: {
      'line-color': '#ff001a',
      'line-width': 8,
      'line-opacity': 0.9,
      'line-blur': 0.5,
    },
  });
}

function formatPropertyTable(props: any): string {
  let table = '<table style="font-size:13px;width:100%;border-collapse:collapse;">';
  for (const [key, value] of Object.entries(props)) {
    table += `
      <tr>
        <td style="font-weight:600;padding:4px 12px 4px 0;text-align:right;vertical-align:top;white-space:nowrap;color:#0074d9;">${key}:</td>
        <td style="padding:4px 0;text-align:left;">${value ?? '<span style=\'color:#aaa;\'>N/A</span>'}</td>
      </tr>
    `;
  }
  table += '</table>';
  return table;
}

// Show info popup with professional design
function showFeaturePopup(e: any, feature: any, displayLength?: number) {
  const props = feature.properties;
  let title = props.name || props.NAME || props.roadname || props.RDNAME || 'Feature Info';

  let html = `
    <div style="min-width:240px;max-width:340px;padding:12px 10px;background:#fff;border-radius:12px;box-shadow:0 6px 16px #0001;color:#222;">
      <div style="font-size:1rem;font-weight:700;margin-bottom:6px;border-bottom:1px solid #e7e7e7;padding-bottom:3px;letter-spacing:.2px;">
        ${title}
      </div>
      ${displayLength !== undefined ? `
      <div style="margin-bottom:8px;font-size:13px;">
        <b>Length covered:</b>
        <span style="color:#ff3b30;font-weight:600;">
          ${displayLength >= 1
            ? displayLength.toLocaleString(undefined, {maximumFractionDigits:2}) + ' km'
            : Math.round(displayLength * 1000) + ' m'}
        </span>
      </div>` : ''}
      ${formatPropertyTable(props)}
    </div>
  `;

  let lngLat = e.lngLat;
  if (feature.geometry.type !== "Point" && feature.geometry.coordinates) {
    try {
      if (feature.geometry.type === "LineString")
        lngLat = feature.geometry.coordinates[Math.floor(feature.geometry.coordinates.length / 2)];
      else if (feature.geometry.type === "Polygon")
        lngLat = feature.geometry.coordinates[0][0];
      else if (feature.geometry.type === "MultiPolygon")
        lngLat = feature.geometry.coordinates[0][0][0];
      else if (feature.geometry.type === "MultiLineString")
        lngLat = feature.geometry.coordinates[0][Math.floor(feature.geometry.coordinates[0].length / 2)];
    } catch {}
  }
  new mapboxgl.Popup()
    .setLngLat(lngLat)
    .setHTML(html)
    .addTo(map!);
}

function toggleLayerVisibility(idx: number) {
  if (!map || !layerIdMap.value[idx]) return;
  const vis = visibleLayers.value[idx] ? 'visible' : 'none';
  for (const lid of layerIdMap.value[idx]) {
    if (map.getLayer(lid)) {
      map.setLayoutProperty(lid, 'visibility', vis);
    }
  }
}

watch([visibleLayers, layerIdMap], ([visibleVal, mapVal]) => {
  if (!map) return;
  Object.entries(mapVal).forEach(([idxStr, ids]) => {
    const idx = Number(idxStr);
    const vis = visibleVal[idx] ? 'visible' : 'none';
    for (const lid of ids) {
      if (map.getLayer(lid)) {
        map.setLayoutProperty(lid, 'visibility', vis);
      }
    }
  });
});

async function loadKmlDynamicLayer(idx: number, kmlUrl: string, color: string) {
  const id = `kml-${idx}`;
  const res = await fetch(kmlUrl);
  const kmlText = await res.text();

  const parser = new DOMParser();
  const kmlDoc = parser.parseFromString(kmlText, 'text/xml');
  const geojson = toGeoJSON.kml(kmlDoc);

  if (!geojson.features.length) return;

  map!.addSource(id, {
    type: 'geojson',
    data: geojson,
  });

  const firstGeom = geojson.features[0].geometry?.type;
  const layerIds: string[] = [];

  if (firstGeom === 'Polygon' || firstGeom === 'MultiPolygon') {
    map!.addLayer({
      id: `${id}-fill`,
      type: 'fill',
      source: id,
      paint: {
        'fill-color': color,
        'fill-opacity': 0.5,
      },
      layout: {
        visibility: visibleLayers.value[idx] ? 'visible' : 'none'
      }
    });
    map!.addLayer({
      id: `${id}-border`,
      type: 'line',
      source: id,
      paint: {
        'line-color': '#1a2637',
        'line-width': 1.5,
      },
      layout: {
        visibility: visibleLayers.value[idx] ? 'visible' : 'none'
      }
    });
    // Popup for polygons
    map!.on('click', `${id}-fill`, (e) => {
      removeHighlightLayer();
      showFeaturePopup(e, e.features[0]);
    });
    map!.on('click', `${id}-border`, (e) => {
      removeHighlightLayer();
      showFeaturePopup(e, e.features[0]);
    });
    map!.on('mouseenter', `${id}-fill`, () => map!.getCanvas().style.cursor = 'pointer');
    map!.on('mouseleave', `${id}-fill`, () => map!.getCanvas().style.cursor = '');
    layerIds.push(`${id}-fill`, `${id}-border`);
  }
  if (firstGeom === 'LineString' || firstGeom === 'MultiLineString') {
    map!.addLayer({
      id: `${id}-line`,
      type: 'line',
      source: id,
      paint: {
        'line-color': color,
        'line-width': 3,
        'line-dasharray': [2, 2],
      },
      layout: {
        visibility: visibleLayers.value[idx] ? 'visible' : 'none'
      }
    });

    // Highlight & info popup for roads
    map!.on('click', `${id}-line`, (e) => {
      // Highlight
      highlightRoad(e.features[0]);
      // Length calculation
      let len_km = 0;
      try {
        len_km = turf.length(e.features[0], { units: 'kilometers' });
      } catch {}
      showFeaturePopup(e, e.features[0], len_km);
    });
    map!.on('mouseenter', `${id}-line`, () => map!.getCanvas().style.cursor = 'pointer');
    map!.on('mouseleave', `${id}-line`, () => map!.getCanvas().style.cursor = '');
    layerIds.push(`${id}-line`);
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
      },
      layout: {
        visibility: visibleLayers.value[idx] ? 'visible' : 'none'
      }
    });
    map!.on('click', `${id}-points`, (e) => {
      removeHighlightLayer();
      showFeaturePopup(e, e.features[0]);
    });
    map!.on('mouseenter', `${id}-points`, () => map!.getCanvas().style.cursor = 'pointer');
    map!.on('mouseleave', `${id}-points`, () => map!.getCanvas().style.cursor = '');
    layerIds.push(`${id}-points`);
  }

  // Track all MapboxGL layer ids
  layerIdMap.value = { ...layerIdMap.value, [idx]: layerIds };
  nextTick(() => toggleLayerVisibility(idx));
}

onMounted(async () => {
  if (!mapContainer.value) return;

  const lat = props.lat ?? DEFAULT_LAT;
  const lng = props.lng ?? DEFAULT_LNG;
  const zoom = props.zoom ?? DEFAULT_ZOOM;

  map = new mapboxgl.Map({
    container: mapContainer.value,
    style: 'mapbox://styles/mapbox/satellite-streets-v11',
    center: [lng, lat],
    zoom,
  });

  map.addControl(new mapboxgl.NavigationControl());

  map.on('load', async () => {
    if (props.kmlLayers && props.kmlLayers.length) {
      for (let i = 0; i < props.kmlLayers.length; i++) {
        const { url, color } = props.kmlLayers[i];
        await loadKmlDynamicLayer(i, url, color);
      }
    }
  });
});

onBeforeUnmount(() => {
  map?.remove();
  map = null;
});
</script>
