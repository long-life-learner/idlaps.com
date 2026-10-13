<script setup>
import { computed, ref } from 'vue';
import { Search, X, ArrowUpRight, MapPin, SearchX } from '@lucide/vue';
import { filterEvents } from '../lib/results';
const props = defineProps({ events: { type: Array, required: true }, mode: { type: String, default: 'events' } });
const query = ref('');
const sport = ref('');
const year = ref('');
const years = computed(() => [...new Set(props.events.map(event => event.year))].sort().reverse());
const filtered = computed(() => filterEvents(props.events, { query: query.value, sport: sport.value, year: year.value }));
const hasFilters = computed(() => query.value || sport.value || year.value);
function reset() { query.value = ''; sport.value = ''; year.value = ''; }
</script>

<template>
    <div class="filter-toolbar">
        <div class="search-field">
            <Search aria-hidden="true" />
            <input v-model="query" type="search" :placeholder="mode === 'results' ? 'Cari nama event atau lokasi…' : 'Cari event atau lokasi…'" aria-label="Cari event atau lokasi" maxlength="150" />
            <button v-if="query" class="search-clear" @click="query = ''" aria-label="Hapus pencarian"><X /></button>
        </div>
        <div class="filter-chips" aria-label="Jenis olahraga">
            <button v-for="item in [['', 'Semua'], ['Running', 'Running'], ['Cycling', 'Cycling']]" :key="item[0]" :class="{ selected: sport === item[0] }" :aria-pressed="sport === item[0]" @click="sport = item[0]">{{ item[1] }}</button>
        </div>
        <div class="filter-selects"><label><span>Tahun event</span><select v-model="year" aria-label="Tahun event"><option value="">Semua tahun</option><option v-for="item in years" :key="item">{{ item }}</option></select></label></div>
    </div>
    <div class="directory-summary"><span role="status">{{ filtered.length }} event ditemukan</span><button v-if="hasFilters" @click="reset">Reset filter</button><span v-else>TERBARU LEBIH DAHULU</span></div>
    <div v-if="!filtered.length" class="empty-state"><SearchX aria-hidden="true" /><h3>Event belum ditemukan.</h3><p>Coba nama lain atau ubah pilihan tahun dan olahraga.</p><button class="button button-outline" @click="reset">Tampilkan semua event</button></div>
    <div v-else-if="mode === 'events'" class="event-grid">
        <article v-for="event in filtered" :key="event.slug" class="event-card">
            <a :href="event.url" class="event-photo" :class="{ 'event-photo-graphic': !event.image }" :aria-label="`Lihat ${event.name}`">
                <img v-if="event.image" :src="event.image" :style="{ objectPosition: event.image_position || 'center' }" :alt="`Dokumentasi ${event.name}`" loading="lazy" width="640" height="460" />
                <span v-else class="graphic-event-title">INI RUN<br><em>FEST.</em></span>
                <span class="image-badge">{{ event.sport }}</span><span class="card-arrow"><ArrowUpRight :size="20" /></span>
            </a>
            <div class="event-card-meta"><span>{{ event.date }}</span><span>EVENT SELESAI</span></div>
            <h3><a :href="event.url">{{ event.name }}</a></h3>
            <p class="location"><MapPin :size="13" aria-hidden="true" /> {{ event.location }}</p>
        </article>
    </div>
    <div v-else class="result-event-list">
        <article v-for="event in filtered" :key="event.slug" class="result-event-row">
            <img v-if="event.image" :src="event.image" :style="{ objectPosition: event.image_position || 'center' }" :alt="`Dokumentasi ${event.name}`" class="result-event-image" width="120" height="80" loading="lazy" />
            <div v-else class="result-event-image event-photo-graphic" aria-hidden="true">INI RUN.</div>
            <div class="result-event-content"><p class="eyebrow">{{ event.sport }} / {{ event.date }}</p><h3><a :href="event.url">{{ event.name }}</a></h3><p class="location"><MapPin :size="13" aria-hidden="true" /> {{ event.location }}</p></div>
            <span class="result-event-year">{{ event.year }}</span><a :href="event.result_url" target="_blank" rel="noopener" class="button button-outline" :aria-label="`Buka hasil ${event.name} di tab baru`">Lihat hasil <ArrowUpRight :size="16" /></a>
        </article>
    </div>
</template>
