import '../css/app.css';
import { initSiteHeader } from './site-header.js';

initSiteHeader();

if (document.getElementById('directory-map')) {
    import('./directory-map.js').then((m) => m.initDirectoryMap());
}

if (document.getElementById('clay-game-canvas')) {
    import('./clay-game.js').then((m) => m.initClayGame());
}

if (document.querySelector('[data-directory-geo]')) {
    import('./directory-geo.js').then((m) => m.initDirectoryGeo());
}

if (document.querySelector('[data-competitions-geo]')) {
    import('./competitions-geo.js').then((m) => m.initCompetitionsGeo());
}

if (document.querySelector('[data-home-search-autocomplete]')) {
    import('./home-search-autocomplete.js').then((m) => m.initHomeSearchAutocomplete());
}
