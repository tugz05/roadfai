<template>
  <div ref="mapContainer" class="w-full h-full min-h-[400px] rounded-xl border" />
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';
import mapboxgl from 'mapbox-gl';
import * as toGeoJSON from '@tmcw/togeojson';
import { DOMParser } from 'xmldom';

interface Props {
  landuseKmlUrl: string;
  roadsKmlUrl: string;
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

mapboxgl.accessToken = 'pk.eyJ1Ijoibm9vYnR1Z3oiLCJhIjoiY21kZHdvaDZ3MDkxMDJscHFjbTNzbWNkciJ9.pUXsJVW32G9zpHypFNT1-g';

/**
 * Load a KML file, convert to GeoJSON, and add it with style based on geometry type
 */
async function loadKmlDynamicLayer(id: string, kmlUrl: string) {
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

  if (firstGeom === 'Polygon' || firstGeom === 'MultiPolygon') {
    map!.addLayer({
      id: `${id}-fill`,
      type: 'fill',
      source: id,
      paint: {
        'fill-color': '#66c2a5',
        'fill-opacity': 0.5,
      },
    });
    map!.addLayer({
      id: `${id}-border`,
      type: 'line',
      source: id,
      paint: {
        'line-color': '#444',
        'line-width': 1,
      },
    });
  } else if (firstGeom === 'LineString' || firstGeom === 'MultiLineString') {
    map!.addLayer({
      id: `${id}-line`,
      type: 'line',
      source: id,
      paint: {
        'line-color': '#fc8d62',
        'line-width': 2,
        'line-dasharray': [2, 2],
      },
    });
  } else if (firstGeom === 'Point' || firstGeom === 'MultiPoint') {
    map!.addLayer({
      id: `${id}-points`,
      type: 'circle',
      source: id,
      paint: {
        'circle-color': '#7570b3',
        'circle-radius': 6,
        'circle-stroke-width': 1,
        'circle-stroke-color': '#fff',
      },
    });
  }

  // Optional: popups
  map!.on('click', id, (e) => {
    const feature = e.features?.[0];
    const coords = feature?.geometry?.type === 'Point'
      ? (feature.geometry as any).coordinates
      : (feature?.geometry as any)?.coordinates?.[0]?.[0];

    const name = feature?.properties?.name || 'Feature';
    if (coords) {
      new mapboxgl.Popup()
        .setLngLat(coords)
        .setHTML(`<strong>${name}</strong>`)
        .addTo(map!);
    }
  });

  map!.on('mouseenter', id, () => {
    map!.getCanvas().style.cursor = 'pointer';
  });
  map!.on('mouseleave', id, () => {
    map!.getCanvas().style.cursor = '';
  });
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
    await loadKmlDynamicLayer('landuse', props.landuseKmlUrl);
    await loadKmlDynamicLayer('roads', props.roadsKmlUrl);
  });
});

onBeforeUnmount(() => {
  map?.remove();
  map = null;
});
</script>
