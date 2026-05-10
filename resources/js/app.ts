import '../css/app.css';
import App from './App.svelte';
import { mount } from 'svelte';

const projectName = document.title.split(' — ')[0] ?? 'My Project';

mount(App, {
    target: document.getElementById('app')!,
    props: { projectName },
});
