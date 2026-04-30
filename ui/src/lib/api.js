const base = () => {
    const path = window.__sentinel?.basePath ?? 'sentinel';
    return `/${path}/api`;
};

const headers = () => ({
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': window.__sentinel?.csrfToken ?? '',
});

export async function fetchSystem() {
    // TODO: GET /api/system
}

export async function fetchJobs(params = {}) {
    // TODO: GET /api/jobs
    // params: { status, page }
}

export async function retryJob(id) {
    // TODO: POST /api/jobs/:id/retry
}

export async function fetchScheduler() {
    // TODO: GET /api/scheduler
}

export async function fetchLogs(params = {}) {
    // TODO: GET /api/logs
    // params: { level, page }
}

export async function deleteLogs() {
    // TODO: DELETE /api/logs
}
