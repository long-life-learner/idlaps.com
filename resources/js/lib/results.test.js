import test from 'node:test';
import assert from 'node:assert/strict';
import { filterEvents, filterResults, formatTime, rankResults } from './results.js';

test('search preserves category rank and leading zero BIBs', () => {
    const rows = rankResults([
        { name: 'Ayu', bib: '0001', category: '5K', status: 'FINISH', seconds: 1300 },
        { name: 'Dimas', bib: '0002', category: '5K', status: 'FINISH', seconds: 1200 },
        { name: 'Ayu', bib: '0101', category: '10K', status: 'FINISH', seconds: 2000 },
    ]);
    const match = filterResults(rows, { query: '  AYU ', category: '5K' });
    assert.equal(match.length, 1);
    assert.equal(match[0].rank, 2);
    assert.equal(match[0].bib, '0001');
    assert.equal(filterResults(rows, { query: '0002' })[0].name, 'Dimas');
});

test('non-finishers never receive ranks, even if a time is present', () => {
    const rows = rankResults([
        { name: 'A', bib: '01', category: '5K', status: 'DNF', seconds: 10 },
        { name: 'B', bib: '02', category: '5K', status: 'FINISH', seconds: 60 },
        { name: 'C', bib: '03', category: '5K', status: 'DNS', seconds: null },
    ]);
    assert.equal(rows[0].bib, '02');
    assert.equal(rows[0].rank, 1);
    assert.equal(filterResults(rows, { status: 'DNF' })[0].rank, null);
    assert.equal(filterResults(rows, { status: 'DNS' })[0].rank, null);
});

test('equal times require review rather than a fabricated tie-break', () => {
    const rows = rankResults([
        { bib: '01', category: '5K', status: 'FINISH', seconds: 60 },
        { bib: '02', category: '5K', status: 'FINISH', seconds: 60 },
        { bib: '03', category: '5K', status: 'FINISH', seconds: 70 },
    ]);
    assert.deepEqual(rows.map(row => row.rank), [null, null, 3]);
    assert.deepEqual(rows.map(row => row.needsReview), [true, true, false]);
});

test('duration format supports over 24 hours and never turns missing time into zero', () => {
    assert.equal(formatTime(null), '—');
    assert.equal(formatTime(-1), '—');
    assert.equal(formatTime(0), '00:00:00');
    assert.equal(formatTime(176523), '49:02:03');
});

test('event search combines city, year and sport filters', () => {
    const events = [
        { name: 'Run A', location: 'Jakarta', year: '2026', sport: 'Running' },
        { name: 'Ride B', location: 'Jakarta', year: '2025', sport: 'Cycling' },
        { name: 'Run C', location: 'Bandung', year: '2026', sport: 'Running' },
    ];
    assert.deepEqual(filterEvents(events, { query: ' jakarta ', year: '2026', sport: 'Running' }).map(row => row.name), ['Run A']);
    assert.equal(filterEvents(events, { query: 'unknown' }).length, 0);
});
