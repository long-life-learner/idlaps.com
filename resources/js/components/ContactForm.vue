<script setup>
import { computed, ref } from 'vue';
import { ArrowUpRight, Copy, Check } from '@lucide/vue';
const props = defineProps({ email: { type: String, required: true }, cc: { type: String, required: true } });
const services = ['Race timing', 'BIB & chip', 'Registrasi peserta', 'Result & e-certificate', 'Beberapa layanan / belum pasti'];
const requested = new URLSearchParams(window.location.search).get('layanan');
const name = ref('');
const event = ref('');
const location = ref('');
const date = ref('');
const service = ref(services.includes(requested) ? requested : services[0]);
const notes = ref('');
const showDraft = ref(false);
const copyStatus = ref('');
const draft = computed(() => `Halo tim IDLAPS,\n\nSaya ${name.value.trim()} ingin berkonsultasi tentang event berikut:\n\nNama event: ${event.value.trim()}\nLokasi: ${location.value.trim()}\nTanggal: ${date.value || 'Belum ditentukan'}\nLayanan: ${service.value}\n\n${notes.value.trim() || 'Mohon informasi mengenai layanan yang sesuai untuk event kami.'}\n\nTerima kasih,\n${name.value.trim()}`);
const mailto = computed(() => `mailto:${props.email}?cc=${encodeURIComponent(props.cc)}&subject=${encodeURIComponent(`Konsultasi event — ${event.value.trim()}`)}&body=${encodeURIComponent(draft.value)}`);
async function copy() {
    try { await navigator.clipboard.writeText(draft.value); copyStatus.value = 'Teks email disalin.'; }
    catch { copyStatus.value = 'Pilih dan salin teks email di atas secara manual.'; }
}
function prepare() { showDraft.value = true; copyStatus.value = ''; }
</script>
<template>
    <form class="contact-form" @submit.prevent="prepare">
        <h2>Ceritakan event Anda.</h2><p>Isi informasi singkat untuk menyiapkan email konsultasi ke tim IDLAPS.</p>
        <div class="form-field"><label for="contact-name">Nama Anda</label><input id="contact-name" v-model="name" required maxlength="100" autocomplete="name" placeholder="Nama lengkap" /></div>
        <div class="form-field"><label for="contact-event">Nama event / penyelenggara</label><input id="contact-event" v-model="event" required maxlength="150" placeholder="Contoh: Community Run 2026" /></div>
        <div class="form-grid"><div class="form-field"><label for="contact-location">Kota / lokasi</label><input id="contact-location" v-model="location" required maxlength="100" placeholder="Lokasi event" /></div><div class="form-field"><label for="contact-date">Tanggal <span>(opsional)</span></label><input id="contact-date" v-model="date" type="date" /></div></div>
        <div class="form-field"><label for="contact-service">Layanan yang dibutuhkan</label><select id="contact-service" v-model="service"><option v-for="item in services" :key="item">{{ item }}</option></select></div>
        <div class="form-field"><label for="contact-notes">Tentang event <span>(opsional)</span></label><textarea id="contact-notes" v-model="notes" maxlength="800" placeholder="Jenis olahraga, perkiraan peserta, kategori, atau kebutuhan khusus…"></textarea></div>
        <button class="button" type="submit">Siapkan email konsultasi <ArrowUpRight :size="18" /></button>
        <p class="form-note">Anda dapat memeriksa draf sebelum mengirimnya melalui aplikasi email. Informasi belum dikirim dari halaman ini.</p>
        <section v-if="showDraft" class="draft-preview" aria-live="polite"><h3>Draf email untuk {{ email }}</h3><p>CC: {{ cc }}</p><pre>{{ draft }}</pre><a :href="mailto" class="button button-outline">Buka aplikasi email <ArrowUpRight :size="18" /></a><button type="button" class="draft-copy" @click="copy"><Check v-if="copyStatus === 'Teks email disalin.'" :size="13" /><Copy v-else :size="13" /> Salin teks email</button><p class="form-note" role="status">{{ copyStatus }}</p></section>
    </form>
</template>
