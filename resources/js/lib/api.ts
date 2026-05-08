const base = (): string => {
    const path = window.__vulcanSentinel?.basePath ?? 'vulcan-sentinel';
    return `/${path}/api`;
};

const headers = (): Record<string, string> => ({
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': window.__vulcanSentinel?.csrfToken ?? '',
});

// region --- System ---------------------------------------------------------------------------------------------------
export async function fetchSystem(): Promise<unknown> {
    const res = await fetch(`${base()}/system`, {headers: headers()});
    return res.json();
}

// endregion

// region --- Runtime --------------------------------------------------------------------------------------------------
export async function fetchRuntime(): Promise<unknown> {
    const res = await fetch(`${base()}/runtime`, {headers: headers()});
    return res.json();
}

// endregion

// region --- Scheduler ------------------------------------------------------------------------------------------------
export async function fetchScheduler(): Promise<unknown> {
    const res = await fetch(`${base()}/scheduler`, {headers: headers()});
    return res.json();
}

// endregion

// region --- Logs ----------------------------------------------------------------------------------------------------

export async function fetchLogEntries(channel: string, cursor: number | null = null, level: string = ''): Promise<unknown> {
    const params = new URLSearchParams({channel, level});
    if (cursor !== null) params.set('cursor', String(cursor));
    const res = await fetch(`${base()}/logs/entries?${params}`, {headers: headers()});
    return res.json();
}

export async function fetchLogTail(channel: string, tailCursor: number, level: string = ''): Promise<unknown> {
    const params = new URLSearchParams({channel, level, tail_cursor: String(tailCursor)});
    const res = await fetch(`${base()}/logs/entries?${params}`, {headers: headers()});
    return res.json();
}

export async function clearLog(channel: string): Promise<unknown> {
    const res = await fetch(`${base()}/logs/clear?channel=${encodeURIComponent(channel)}`, {
        method: 'DELETE',
        headers: headers(),
    });
    return res.json();
}

// endregion

// region --- Queue ---------------------------------------------------------------------------------------------------

// Queue
export async function fetchQueue(): Promise<unknown> {
    const res = await fetch(`${base()}/queue`, {headers: headers()});
    return res.json();
}

// Completed Jobs
export async function deleteCompletedJob(id: string): Promise<unknown> {
    const res = await fetch(`${base()}/queue/completed/${id}`, {
        method: 'DELETE',
        headers: headers(),
    });
    return res.json();
}

export async function clearCompletedJobs(): Promise<unknown> {
    const res = await fetch(`${base()}/queue/completed/clear`, {
        method: 'DELETE',
        headers: headers(),
    });
    return res.json();
}

// Failed Jobs
export async function retryFailedJob(id: string): Promise<unknown> {
    const res = await fetch(`${base()}/queue/failed/${id}/retry`, {
        method: 'POST',
        headers: headers(),
    });
    return res.json();
}

export async function deleteFailedJob(id: string): Promise<unknown> {
    const res = await fetch(`${base()}/queue/failed/${id}`, {
        method: 'DELETE',
        headers: headers(),
    });
    return res.json();
}


export async function clearFailedJobs(): Promise<unknown> {
    const res = await fetch(`${base()}/queue/failed/clear`, {
        method: 'DELETE',
        headers: headers(),
    });
    return res.json();
}

// endregion
