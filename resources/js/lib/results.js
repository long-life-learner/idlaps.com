export function formatTime(seconds) {
    if (!Number.isFinite(seconds) || seconds < 0) return '—';
    return [Math.floor(seconds / 3600), Math.floor(seconds % 3600 / 60), Math.floor(seconds % 60)]
        .map(value => String(value).padStart(2, '0')).join(':');
}

export function rankResults(rows) {
    const groups = new Map();
    rows.forEach(row => {
        if (!groups.has(row.category)) groups.set(row.category, []);
        groups.get(row.category).push(row);
    });
    return [...groups.values()].flatMap(group => {
        const finishers = group.filter(row => row.status === 'FINISH' && Number.isFinite(row.seconds))
            .sort((a, b) => a.seconds - b.seconds || a.bib.localeCompare(b.bib));
        const counts = new Map();
        finishers.forEach(row => counts.set(row.seconds, (counts.get(row.seconds) || 0) + 1));
        return [
            ...finishers.map((row, index) => ({
                ...row,
                rank: counts.get(row.seconds) > 1 ? null : index + 1,
                needsReview: counts.get(row.seconds) > 1,
            })),
            ...group.filter(row => row.status !== 'FINISH' || !Number.isFinite(row.seconds))
                .map(row => ({ ...row, rank: null, needsReview: row.status === 'FINISH' })),
        ];
    });
}

export function filterResults(rows, { query = '', category = '', status = '' } = {}) {
    const search = query.trim().toLocaleLowerCase('id');
    return rows.filter(row => (!category || row.category === category)
        && (!status || row.status === status)
        && (!search || row.name.toLocaleLowerCase('id').includes(search) || row.bib.toLowerCase().includes(search)));
}

export function filterEvents(events, { query = '', sport = '', year = '' } = {}) {
    const search = query.trim().toLocaleLowerCase('id');
    return events.filter(event => (!sport || event.sport === sport)
        && (!year || event.year === year)
        && (!search || `${event.name} ${event.location}`.toLocaleLowerCase('id').includes(search)));
}
