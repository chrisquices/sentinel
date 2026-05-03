// System
export interface SystemData {
    cpu: Cpu;
    memory: Memory;
    storage: Storage;
}

export interface Cpu {
    cores: number;
    coresFormatted: string;
    usage: number;
    usageFormatted: string;
    history: CpuHistory[];
}

export interface CpuHistory {
    time: number;
    timeFormatted: string;
    usage: number;
    usageFormatted: string;
}

export interface Memory {
    total: number;
    totalFormatted: string;
    used: number;
    usedFormatted: string;
    available: number;
    availableFormatted: string;
}

export interface Storage {
    total: number;
    totalFormatted: string;
    used: number;
    usedFormatted: string;
    available: number;
    availableFormatted: string;
}

// Scheduler
export interface SchedulerData {
    events: SchedulerTask[];
}

export interface SchedulerTask {
    command: string;
    expression: string;
    expressionLabel: string;
    nextRun: string;
    lastRanAt: string | null;
    exitCode: number | null;
    status: 'never' | 'success' | 'failed';
}

// Queue
export interface QueueData {
    summary: QueueSummary;
    jobs: Job[];
}

export interface QueueSummary {
    pending: number;
    processing: number;
    completed: number;
    failed: number;
}

export interface Job {
    id: string;
    jobClass: string;
    displayName: string;
    queue: string;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    attempts: number;
    createdAt: string | null;
    createdAtFormatted: string;
    runTime?: number | null;
    runTimeFormatted?: string;
    exception?: string;
    exceptionFull?: string;
}

