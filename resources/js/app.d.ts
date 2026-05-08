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
        };
    }
}

export {};
