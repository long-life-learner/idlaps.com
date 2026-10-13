const names = ['Aditya Pratama', 'Rizky Saputra', 'Dimas Wicaksono', 'Nadia Putri', 'Arif Rahman', 'Sinta Maharani', 'Fajar Nugroho', 'Ayu Lestari', 'Bima Santoso', 'Dewi Anggraini', 'Raka Wijaya', 'Putri Amelia', 'Bagas Kurniawan', 'Nisa Ramadhani', 'Reza Pahlevi', 'Laras Wulandari'];
const cities = ['Jakarta', 'Bandung', 'Bekasi', 'Tangerang', 'Bogor', 'Depok'];
const teams = ['Run Together', 'Sunday Runners', '', 'Morning Miles'];
export const demoResults = ['5K', '10K'].flatMap((category, group) => names.map((name, index) => ({
    bib: String(group * 100 + index + 1).padStart(4, '0'),
    name: group ? names[(index + 5) % names.length] : name,
    team: teams[index % teams.length],
    origin: index % 5 === 0 ? '' : cities[index % cities.length],
    category,
    seconds: index >= 14 ? null : (group ? 2150 : 1032) + index * (group ? 83 : 47),
    status: index === 14 ? 'DNF' : index === 15 ? 'DNS' : 'FINISH',
})));
