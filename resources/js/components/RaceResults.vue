<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { Search, X, ChevronLeft, ChevronRight, ArrowUpRight, Copy, SearchX } from '@lucide/vue';
import { demoResults } from '../lib/demo-results';
import { rankResults, filterResults, formatTime } from '../lib/results';
const ranked = rankResults(demoResults);
const params = new URLSearchParams(window.location.search);
const query = ref((params.get('q') || '').slice(0, 100));
const category = ref(['5K', '10K'].includes(params.get('kategori')) ? params.get('kategori') : '5K');
const status = ref('');
const tab = ref('results');
const page = ref(1);
const pageSize = 8;
const selected = ref(null);
const dialog = ref(null);
const copyStatus = ref('');
const filtered = computed(() => filterResults(ranked, { query: query.value, category: category.value, status: status.value }));
const categoryRows = computed(() => filterResults(ranked, { category: category.value }));
const finishers = computed(() => categoryRows.value.filter(row => row.status === 'FINISH'));
const podium = computed(() => finishers.value.filter(row => row.rank && row.rank <= 3));
const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / pageSize)));
const visible = computed(() => filtered.value.slice((page.value - 1) * pageSize, page.value * pageSize));
const rangeStart = computed(() => filtered.value.length ? (page.value - 1) * pageSize + 1 : 0);
const rangeEnd = computed(() => Math.min(page.value * pageSize, filtered.value.length));
watch([query, category, status], () => {
    page.value = 1;
    const url = new URL(window.location.href);
    if (query.value.trim()) url.searchParams.set('q', query.value.trim()); else url.searchParams.delete('q');
    url.searchParams.set('kategori', category.value);
    url.searchParams.delete('bib');
    window.history.replaceState(null, '', url);
});
function reset() { query.value = ''; status.value = ''; }
async function openResult(row) {
    selected.value = row;
    copyStatus.value = '';
    await nextTick();
    dialog.value.showModal();
}
function closeResult() { dialog.value.close(); }
async function copyLink() {
    const url = new URL(window.location.href);
    url.search = '';
    url.searchParams.set('kategori', selected.value.category);
    url.searchParams.set('bib', selected.value.bib);
    try { await navigator.clipboard.writeText(url.href); copyStatus.value = 'Tautan hasil demo disalin.'; }
    catch { copyStatus.value = `Salin tautan ini: ${url.href}`; }
}
function changePage(amount) { page.value = Math.min(totalPages.value, Math.max(1, page.value + amount)); }
onMounted(() => {
    const bib = params.get('bib');
    const row = ranked.find(row => row.bib === bib && row.category === category.value);
    if (row) openResult(row);
});
</script>

<template>
    <div class="result-tabs" aria-label="Tampilan hasil">
        <button :class="{ selected: tab === 'results' }" :aria-pressed="tab === 'results'" @click="tab = 'results'">Hasil peserta</button>
        <button :class="{ selected: tab === 'podium' }" :aria-pressed="tab === 'podium'" @click="tab = 'podium'">Podium kategori</button>
    </div>
    <div class="result-controls">
        <div v-if="tab === 'results'" class="search-field"><Search aria-hidden="true" /><input v-model="query" type="search" placeholder="Cari nama atau nomor BIB…" aria-label="Cari nama atau nomor BIB" maxlength="100" /><button v-if="query" class="search-clear" @click="query = ''" aria-label="Hapus pencarian peserta"><X /></button></div>
        <div class="filter-selects"><label><span>KATEGORI</span><select v-model="category" aria-label="Kategori lomba"><option>5K</option><option>10K</option></select></label><label v-if="tab === 'results'"><span>STATUS</span><select v-model="status" aria-label="Status peserta"><option value="">Semua status</option><option value="FINISH">Finis</option><option value="DNF">DNF</option><option value="DNS">DNS</option><option value="DSQ">DSQ</option></select></label></div>
    </div>
    <div class="result-overview"><div><span>PESERTA {{ category }}</span><strong>{{ categoryRows.length }}</strong></div><div><span>FINISHER</span><strong>{{ finishers.length }}</strong></div><div><span>WAKTU TERBAIK</span><strong class="is-time">{{ formatTime(finishers[0]?.seconds) }}</strong></div></div>
    <template v-if="tab === 'results'">
        <div v-if="!filtered.length" class="empty-state"><SearchX aria-hidden="true" /><h3>Peserta belum ditemukan.</h3><p>Periksa nomor BIB, ejaan nama, atau kategori yang dipilih.</p><button class="button button-outline" @click="reset">Reset pencarian</button></div>
        <template v-else>
            <div class="results-table-wrap" tabindex="0" role="region" aria-label="Tabel hasil peserta, geser untuk melihat seluruh kolom">
                <table class="results-table"><caption>Hasil simulasi kategori {{ category }}. Bukan hasil lomba resmi.</caption><thead><tr><th scope="col">Pos.</th><th scope="col">BIB</th><th scope="col">Nama / Team</th><th scope="col">Asal</th><th scope="col">Kategori</th><th scope="col">Waktu finis</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Detail</span></th></tr></thead>
                    <tbody><tr v-for="row in visible" :key="row.bib"><td class="rank-cell" :class="{ 'top-rank': row.rank && row.rank <= 3 }">{{ row.rank ? String(row.rank).padStart(2, '0') : '—' }}</td><td class="bib-cell">{{ row.bib }}</td><td><button class="participant-button" @click="openResult(row)" :aria-label="`Lihat hasil ${row.name}, BIB ${row.bib}`"><span class="athlete-name">{{ row.name }}</span><span class="athlete-team">{{ row.team || 'Individu' }}</span></button></td><td class="bib-cell">{{ row.origin || '—' }}</td><td><span class="table-category">{{ row.category }}</span></td><td class="finish-time">{{ formatTime(row.seconds) }}</td><td :class="`status-${row.status.toLowerCase()}`">{{ row.needsReview ? 'PERLU DITINJAU' : row.status }}</td><td><button class="participant-button" @click="openResult(row)" :aria-label="`Detail peserta BIB ${row.bib}`"><ArrowUpRight :size="17" /></button></td></tr></tbody>
                </table>
            </div>
            <div class="table-footer"><span role="status">{{ rangeStart }}–{{ rangeEnd }} dari {{ filtered.length }} peserta</span><nav class="pagination" aria-label="Halaman hasil"><button @click="changePage(-1)" :disabled="page === 1" aria-label="Halaman sebelumnya"><ChevronLeft /></button><span>{{ page }} / {{ totalPages }}</span><button @click="changePage(1)" :disabled="page === totalPages" aria-label="Halaman berikutnya"><ChevronRight /></button></nav></div>
        </template>
    </template>
    <div v-else class="podium-grid"><article v-for="row in podium" :key="row.bib" class="podium-card"><span class="podium-number">{{ String(row.rank).padStart(2, '0') }}</span><h3>{{ row.name }}</h3><p>{{ row.team || 'Individu' }} · BIB {{ row.bib }}</p><p class="podium-time">{{ formatTime(row.seconds) }}</p><button @click="openResult(row)">Lihat catatan waktu <ArrowUpRight :size="13" /></button></article></div>
    <p class="result-method-note">Peringkat ditampilkan per kategori dan tidak berubah saat pencarian. DNF: tidak menyelesaikan lomba · DNS: tidak memulai lomba · DSQ: diskualifikasi. Seluruh nama, tim, dan waktu pada halaman ini adalah data simulasi.</p>
    <dialog ref="dialog" class="result-dialog" aria-labelledby="participant-title" @click="event => { if (event.target === dialog) closeResult(); }">
        <template v-if="selected"><button class="dialog-close" @click="closeResult" aria-label="Tutup detail peserta"><X /></button><p class="eyebrow">IDLAPS DEMO RUN / {{ selected.category }}</p><h2 id="participant-title">{{ selected.name }}</h2><p class="participant-info">BIB {{ selected.bib }} · {{ selected.team || 'Individu' }}<br>{{ selected.origin || 'Asal tidak dicantumkan' }}</p><p class="result-large-time">{{ formatTime(selected.seconds) }}</p><dl><div><dt>PERINGKAT KATEGORI</dt><dd>{{ selected.rank ? '#' + selected.rank : '—' }}</dd></div><div><dt>STATUS</dt><dd>{{ selected.needsReview ? 'Perlu ditinjau' : selected.status }}</dd></div></dl><button class="button button-outline" @click="copyLink">Salin tautan hasil demo <Copy :size="16" /></button><p class="copy-status" role="status">{{ copyStatus }}</p><p class="dialog-notice">Data simulasi untuk pratinjau. Catatan waktu ini bukan hasil lomba resmi.</p></template>
    </dialog>
</template>
