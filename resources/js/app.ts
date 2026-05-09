import '../css/app.css';
import App from './App.svelte';
import { mount } from 'svelte';

mount(App, {
    target: document.getElementById('app')!,
    props: {
        projectName: window.__sentinel?.projectName ?? 'My Project',
    },
});
