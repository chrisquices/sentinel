const base = (): string => {
    const pathname = window.location.pathname.replace(/\/+$/, '');
    return `${pathname}/api`;
};

const headers = (): Record<string, string> => ({
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
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

export async function fetchLogs(): Promise<unknown> {
    const res = await fetch(`${base()}/logs`, {headers: headers()});
    return res.json();
}

export async function fetchLogEntries(channel: string, page: number = 1, level: string = '', search: string = ''): Promise<unknown> {
    const params = new URLSearchParams({ page: String(page), level, search });
    const res = await fetch(`${base()}/logs/${encodeURIComponent(channel)}/entries?${params}`, {headers: headers()});
    return res.json();
}

export async function fetchLogTail(channel: string, tailCursor: number, level: string = ''): Promise<unknown> {
    const params = new URLSearchParams({ tailCursor: String(tailCursor), level });
    const res = await fetch(`${base()}/logs/${encodeURIComponent(channel)}/tail?${params}`, {headers: headers()});
    return res.json();
}

export async function clearLog(channel: string): Promise<unknown> {
    const res = await fetch(`${base()}/logs/${encodeURIComponent(channel)}/clear`, {
        method: 'DELETE',
        headers: headers(),
    });
    return res.json();
}

// endregion

// region --- Queue ---------------------------------------------------------------------------------------------------

// Queue
export async function fetchJobPayload(id: string): Promise<unknown> {
    const res = await fetch(`${base()}/queue/jobs/${encodeURIComponent(id)}`, {headers: headers()});
    return res.json();
}

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
