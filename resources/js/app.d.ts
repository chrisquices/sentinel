declare global {
    interface Window {
        __vulcanSentinel?: {
            projectName: string;
            basePath: string;
            csrfToken: string;
        };
    }
}

export {};
