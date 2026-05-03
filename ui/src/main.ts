import './app.css';
import App from './App.svelte';
import { mount } from 'svelte';

mount(App, {
    target: document.getElementById('app')!,
    props: {
        projectName: window.__vulcanSentinel?.projectName ?? 'My Project',
    },
});
