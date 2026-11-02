/**
 * Inter Roll public site. Pages are server-rendered Blade; Alpine.js adds the interactive parts.
 * Every component lives in its own module under ./site and is registered here by name
 * (x-data="catalog", x-data="leadForm({...})" …).
 */
import Alpine from 'alpinejs';

import { startMeshBackground } from './site/effects/mesh-background.js';
import { t } from './site/core/i18n.js';
import { toast } from './site/core/toast.js';
import reveal from './site/directives/reveal.js';
import countUp from './site/directives/count-up.js';

import siteHeader from './site/components/site-header.js';
import leadForm from './site/components/lead-form.js';
import compare from './site/components/compare.js';
import copyButton from './site/components/copy-button.js';
import filterList from './site/components/filter-list.js';
import { heroRoller, productStory } from './site/pages/home.js';
import catalog from './site/pages/catalog.js';
import productPage from './site/pages/product.js';
import recoating from './site/pages/recoating.js';
import { contactForm, openingStatus } from './site/pages/contact.js';
import partFinder from './site/pages/part-finder/part-finder.js';
import videoPlayer from './site/pages/video.js';
import { shoreScale, tableOfContents } from './site/pages/post.js';

Alpine.plugin(reveal);
Alpine.plugin(countUp);

const components = {
    siteHeader,
    leadForm,
    compare,
    copyButton,
    filterList,
    heroRoller,
    productStory,
    catalog,
    productPage,
    recoating,
    openingStatus,
    contactForm,
    partFinder,
    videoPlayer,
    tableOfContents,
    shoreScale,
};

for (const [name, component] of Object.entries(components)) {
    Alpine.data(name, component);
}

window.Alpine = Alpine;
Alpine.start();

const mesh = document.querySelector('canvas[data-mesh]');
if (mesh) startMeshBackground(mesh);

// A form sent without JavaScript came back with its request number.
if (document.getElementById('lead-flash')) toast(t('lead.sent'));
