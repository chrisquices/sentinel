declare global {
    interface Window {
        __vulcanSentinel?: {
            projectName: string;
            basePath: string;
            csrfToken: string;
            system?: unknown;
            scheduler?: unknown;
            queue?: unknown;
            channels?: unknown;
            initialLogData?: {
                entries: unknown[];
                cursor: number | null;
                hasMore: boolean;
                tailCursor: number;
            } | null;
            initialLogPage?: number;
        };
    }
}

export {};
