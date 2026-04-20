import { initOwnerCompetitionSquads } from './owner-competition-squads.js';

const root = document.querySelector('[data-owner-competition-squads]');
if (root) {
    initOwnerCompetitionSquads(root);
}
