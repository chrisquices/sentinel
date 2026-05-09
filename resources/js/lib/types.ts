// System
export type SystemInitialData = SystemData;

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

// Runtime
export interface RuntimeInfo {
    phpVersion: string;
    sapi: string;
    memoryLimit: string;
    maxExecutionTime: string;
    uploadMaxFilesize: string;
    postMaxSize: string;
    opcache: OpcacheInfo;
}

export interface OpcacheInfo {
    enabled: boolean;
    hitRatio: number | null;
    memoryUsed: string | null;
    memoryFree: string | null;
    cachedScripts: number | null;
}

// Scheduler
export interface SchedulerInitialData {
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
export interface QueueInitialData {
    summary: QueueSummary;
    jobs: Job[];
}

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

// Logs
export interface LogInitialData {
    channels: LogChannel[];
    entries: LogEntry[];
    total: number;
    tailCursor: number;
}

export interface LogChannel {
    name: string;
    driver: string;
    path: string;
}

export interface LogEntry {
    timestamp: string | null;
    timestampFormatted: string | null;
    level: string;
    message: string;
    extra: string | null;
}

export interface LogEntriesResult {
    entries: LogEntry[];
    total: number;
    tailCursor: number;
}

export interface LogTailResult {
    entries: LogEntry[];
    tailCursor: number;
}