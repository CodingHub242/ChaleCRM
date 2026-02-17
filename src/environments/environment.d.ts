export interface Environment {
  production: boolean;
  apiUrl: string;
  wsUrl?: string;
}

declare const environment: Environment;

export { environment };
