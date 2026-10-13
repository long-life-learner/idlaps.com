import { createApp } from 'vue';
import EventDirectory from './components/EventDirectory.vue';
import RaceResults from './components/RaceResults.vue';
import ContactForm from './components/ContactForm.vue';

const directory = document.querySelector('[data-component="event-directory"]');
if (directory) createApp(EventDirectory, { events: window.idlapsEvents || [], mode: directory.dataset.mode }).mount(directory);
const results = document.querySelector('[data-component="race-results"]');
if (results) createApp(RaceResults).mount(results);
const contact = document.querySelector('[data-component="contact-form"]');
if (contact) createApp(ContactForm, { email: contact.dataset.email }).mount(contact);

const menu = document.querySelector('.menu-toggle');
const mobileNav = document.querySelector('#mobile-nav');
function closeMenu() {
    mobileNav.hidden = true;
    menu.setAttribute('aria-expanded', 'false');
    menu.setAttribute('aria-label', 'Buka menu');
}
menu?.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    mobileNav.hidden = !open;
});
mobileNav?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && menu?.getAttribute('aria-expanded') === 'true') {
        closeMenu(); menu.focus();
    }
});
window.matchMedia('(min-width: 601px)').addEventListener('change', event => { if (event.matches) closeMenu(); });

const video = document.querySelector('[data-video-hero] video');
const toggle = document.querySelector('[data-video-toggle]');
if (video && toggle) {
    let sourceLoaded = false;
    let manuallyPaused = false;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    function syncButton() {
        const isPlaying = !video.paused;
        toggle.setAttribute('aria-label', isPlaying ? 'Jeda video latar' : 'Putar video latar');
        toggle.querySelector('[data-video-symbol]').textContent = isPlaying ? 'Ⅱ' : '▶';
        toggle.querySelector('[data-video-label]').textContent = isPlaying ? 'Jeda video' : 'Putar video';
    }
    async function play() {
        if (!sourceLoaded) {
            video.querySelector('source').src = video.querySelector('source').dataset.src;
            video.load(); sourceLoaded = true;
        }
        try { await video.play(); } catch { syncButton(); }
    }
    video.addEventListener('play', syncButton);
    video.addEventListener('pause', syncButton);
    toggle.addEventListener('click', () => {
        if (video.paused) { manuallyPaused = false; play(); }
        else { manuallyPaused = true; video.pause(); }
    });
    const observer = new IntersectionObserver(entries => {
        if (!entries[0].isIntersecting) video.pause();
        else if (!manuallyPaused && !reducedMotion.matches && !navigator.connection?.saveData) play();
    }, { threshold: 0.15 });
    observer.observe(video);
    reducedMotion.addEventListener('change', event => { if (event.matches) video.pause(); });
}
